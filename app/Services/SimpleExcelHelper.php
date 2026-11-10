<?php

namespace App\Services;

use ZipArchive;
use Symfony\Component\HttpFoundation\Response;

class SimpleExcelHelper
{
    /**
     * Parse file upload (.xlsx, .csv) menjadi array baris data (2D array)
     */
    public function parseFile(string $filePath, ?string $originalName = null): array
    {
        $ext = strtolower(pathinfo($originalName ?: $filePath, PATHINFO_EXTENSION));

        if ($ext === 'csv' || $ext === 'txt') {
            return $this->parseCsv($filePath);
        }

        return $this->parseXlsx($filePath);
    }

    /**
     * Parse file XLSX menggunakan ZipArchive dan SimpleXML
     */
    public function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('File Excel (.xlsx) tidak dapat dibuka atau rusak.');
        }

        // 1. Baca sharedStrings.xml jika ada
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $xml = @simplexml_load_string($ssXml);
            if ($xml) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string)$r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Baca worksheet pertama (sheet1.xml)
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            throw new \RuntimeException('Sheet data tidak ditemukan di file XLSX.');
        }

        $xml = @simplexml_load_string($sheetXml);
        if (!$xml) {
            $zip->close();
            throw new \RuntimeException('Gagal memproses struktur XML pada file Excel.');
        }

        $rows = [];
        if (isset($xml->sheetData->row)) {
            foreach ($xml->sheetData->row as $rowElem) {
                $rowData = [];
                $maxCol = -1;

                foreach ($rowElem->c as $cell) {
                    $ref = (string)$cell['r'];
                    $colIdx = $this->colRefToIndex($ref);

                    $val = '';
                    $type = (string)$cell['t'];

                    if ($type === 's') {
                        $sIdx = (int)(string)$cell->v;
                        $val = $sharedStrings[$sIdx] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $val = (string)($cell->is->t ?? '');
                    } elseif (isset($cell->v)) {
                        $val = (string)$cell->v;
                    }

                    $rowData[$colIdx] = trim($val);
                    if ($colIdx > $maxCol) {
                        $maxCol = $colIdx;
                    }
                }

                // Isi sel yang kosong agar indeks kolom konsisten
                $completeRow = [];
                for ($c = 0; $c <= $maxCol; $c++) {
                    $completeRow[$c] = $rowData[$c] ?? '';
                }

                // Jangan masukkan baris yang benar-benar kosong
                $hasContent = false;
                foreach ($completeRow as $cVal) {
                    if ($cVal !== '') {
                        $hasContent = true;
                        break;
                    }
                }

                if ($hasContent) {
                    $rows[] = $completeRow;
                }
            }
        }

        $zip->close();
        return $rows;
    }

    /**
     * Parse file CSV dengan deteksi otomatis delimiter (, atau ;)
     */
    public function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Gagal membaca file CSV.');
        }

        // Deteksi delimiter dari baris pertama
        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = ',';
        if ($firstLine !== false) {
            $semicolons = substr_count($firstLine, ';');
            $commas = substr_count($firstLine, ',');
            $tabs = substr_count($firstLine, "\t");
            if ($semicolons > $commas && $semicolons > $tabs) {
                $delimiter = ';';
            } elseif ($tabs > $commas && $tabs > $semicolons) {
                $delimiter = "\t";
            }
        }

        $rows = [];
        $isFirst = true;

        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            // Bersihkan UTF-8 BOM pada sel pertama
            if ($isFirst && isset($data[0])) {
                $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
                $isFirst = false;
            }

            $trimmedRow = array_map('trim', $data);

            $hasContent = false;
            foreach ($trimmedRow as $v) {
                if ($v !== '') {
                    $hasContent = true;
                    break;
                }
            }

            if ($hasContent) {
                $rows[] = $trimmedRow;
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Konversi koordinat cell (misal: "A1", "BC12") menjadi indeks kolom 0-based
     */
    private function colRefToIndex(string $ref): int
    {
        preg_match('/^([A-Z]+)/', strtoupper($ref), $m);
        $letters = $m[1] ?? 'A';
        $num = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $num = $num * 26 + (ord($letters[$i]) - 64);
        }
        return max(0, $num - 1);
    }

    /**
     * Buat file XLSX sederhana dan rapi (SPL Pink Theme)
     */
    public function createXlsx(string $sheetName, array $headers, array $rows, ?string $title = null, ?string $subtitle = null, array $colWidths = []): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'spl_tpl_');
        $zip = new ZipArchive();

        if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file template XLSX.');
        }

        $safeSheetName = mb_substr(preg_replace('/[\\\\\\/\?\*\:\[\]]/', '', $sheetName), 0, 31);

        // Content Types
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n" .
            '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n" .
            '  <Default Extension="xml" ContentType="application/xml"/>' . "\n" .
            '  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n" .
            '  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . "\n" .
            '  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n" .
            '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // Root Rels
        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n" .
            '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' . "\n" .
            '</Relationships>';
        $zip->addFromString('_rels/.rels', $rootRels);

        // Workbook Rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n" .
            '  <Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' . "\n" .
            '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' . "\n" .
            '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // Workbook
        $safeXmlSheet = htmlspecialchars($safeSheetName, ENT_XML1, 'UTF-8');
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n" .
            '  <sheets>' . "\n" .
            "    <sheet name=\"{$safeXmlSheet}\" sheetId=\"1\" r:id=\"rId1\"/>\n" .
            '  </sheets>' . "\n" .
            '</workbook>';
        $zip->addFromString('xl/workbook.xml', $wb);

        // Styles (Berry Pink Header #BE185D, Soft Pink, Border)
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n" .
'  <fonts count="5">' . "\n" .
'    <font><sz val="10"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="13"/><color rgb="FF831843"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="10"/><color rgb="FF0F172A"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><sz val="9"/><color rgb="FF64748B"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'  </fonts>' . "\n" .
'  <fills count="4">' . "\n" .
'    <fill><patternFill patternType="none"/></fill>' . "\n" .
'    <fill><patternFill patternType="gray125"/></fill>' . "\n" .
'    <fill><patternFill patternType="solid"><fgColor rgb="FFBE185D"/><bgColor indexed="64"/></patternFill></fill>' . "\n" .
'    <fill><patternFill patternType="solid"><fgColor rgb="FFFCE7F1"/><bgColor indexed="64"/></patternFill></fill>' . "\n" .
'  </fills>' . "\n" .
'  <borders count="2">' . "\n" .
'    <border><left/><right/><top/><bottom/><diagonal/></border>' . "\n" .
'    <border>' . "\n" .
'      <left style="thin"><color rgb="FFCBD5E1"/></left>' . "\n" .
'      <right style="thin"><color rgb="FFCBD5E1"/></right>' . "\n" .
'      <top style="thin"><color rgb="FFCBD5E1"/></top>' . "\n" .
'      <bottom style="thin"><color rgb="FFCBD5E1"/></bottom>' . "\n" .
'    </border>' . "\n" .
'  </borders>' . "\n" .
'  <cellStyleXfs count="1">' . "\n" .
'    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' . "\n" .
'  </cellStyleXfs>' . "\n" .
'  <cellXfs count="6">' . "\n" .
'    <!-- 0: normal -->' . "\n" .
'    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' . "\n" .
'    <!-- 1: title -->' . "\n" .
'    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>' . "\n" .
'    <!-- 2: subtitle -->' . "\n" .
'    <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>' . "\n" .
'    <!-- 3: header (pink fill, white bold, border, center) -->' . "\n" .
'    <xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' . "\n" .
'      <alignment horizontal="center" vertical="center" wrapText="1"/>' . "\n" .
'    </xf>' . "\n" .
'    <!-- 4: data (border, left) -->' . "\n" .
'    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">' . "\n" .
'      <alignment horizontal="left" vertical="center"/>' . "\n" .
'    </xf>' . "\n" .
'    <!-- 5: meta note -->' . "\n" .
'    <xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1"/>' . "\n" .
'  </cellXfs>' . "\n" .
'</styleSheet>';
        $zip->addFromString('xl/styles.xml', $styles);

        // Sheet1 XML
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n" .
            '  <sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>' . "\n" .
            '  <sheetFormatPr defaultRowHeight="20"/>' . "\n";

        // Columns width
        $sheetXml .= '  <cols>' . "\n";
        $colCount = max(count($headers), 1);
        for ($c = 1; $c <= $colCount; $c++) {
            $w = $colWidths[$c] ?? 20;
            $sheetXml .= "    <col min=\"{$c}\" max=\"{$c}\" width=\"{$w}\" customWidth=\"1\"/>\n";
        }
        $sheetXml .= '  </cols>' . "\n";

        $sheetXml .= '  <sheetData>' . "\n";
        $r = 1;

        if ($title) {
            $sheetXml .= "    <row r=\"{$r}\" ht=\"24\">\n";
            $sheetXml .= $this->cellStr('A', $r, $title, 1);
            $sheetXml .= "    </row>\n";
            $r++;
        }

        if ($subtitle) {
            $sheetXml .= "    <row r=\"{$r}\" ht=\"18\">\n";
            $sheetXml .= $this->cellStr('A', $r, $subtitle, 5);
            $sheetXml .= "    </row>\n";
            $r++;
        }

        if ($title || $subtitle) {
            $sheetXml .= "    <row r=\"{$r}\" ht=\"10\"/>\n";
            $r++;
        }

        // Header Row
        $sheetXml .= "    <row r=\"{$r}\" ht=\"26\">\n";
        $colIdx = 1;
        foreach ($headers as $h) {
            $colLetter = $this->indexToColLetter($colIdx++);
            $sheetXml .= $this->cellStr($colLetter, $r, (string)$h, 3);
        }
        $sheetXml .= "    </row>\n";
        $r++;

        // Data Rows
        foreach ($rows as $row) {
            $sheetXml .= "    <row r=\"{$r}\" ht=\"20\">\n";
            $colIdx = 1;
            foreach ($row as $val) {
                $colLetter = $this->indexToColLetter($colIdx++);
                $sheetXml .= $this->cellStr($colLetter, $r, (string)$val, 4);
            }
            $sheetXml .= "    </row>\n";
            $r++;
        }

        $sheetXml .= '  </sheetData>' . "\n";
        $sheetXml .= '  <printOptions gridLines="1"/>' . "\n";
        $sheetXml .= '  <pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $sheetXml .= '  <pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>' . "\n";
        $sheetXml .= '</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $content = file_get_contents($tmpFile);
        @unlink($tmpFile);

        return $content;
    }

    /**
     * Buat file CSV UTF-8 dengan BOM
     */
    public function createCsv(array $headers, array $rows): string
    {
        $fp = fopen('php://temp', 'r+');
        // UTF-8 BOM agar terbaca di Excel tanpa encoding rusak
        fputs($fp, "\xEF\xBB\xBF");

        fputcsv($fp, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($fp, $row, ';');
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return $csv;
    }

    private function cellStr(string $col, int $row, string $value, int $styleId = 0): string
    {
        $escaped = htmlspecialchars($value, ENT_XML1, 'UTF-8');
        return "      <c r=\"{$col}{$row}\" t=\"inlineStr\" s=\"{$styleId}\"><is><t>{$escaped}</t></is></c>\n";
    }

    private function indexToColLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $mod = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colIndex = intdiv($colIndex - $mod, 26);
        }
        return $letter;
    }
}

