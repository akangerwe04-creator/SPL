<?php

namespace App\Services;

use ZipArchive;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CatalogSetting;

class WholesaleProcurementExportService
{
    /**
     * Ekspor Laporan Rekapitulasi Pengadaan Barang & Kemasan ke Supplier (Format Excel Multi-Sheet .xlsx)
     */
    public function exportXlsx(array $plannerData, string $scope = 'all'): Response
    {
        $appSetting = CatalogSetting::current();
        $appName = $appSetting->app_name ?: 'SPL (Susi Parsel Lebaran)';
        $programPeriod = $appSetting->program_period ?: 'Musim Lebaran 1447 H / 2026';
        $printedDate = date('d F Y H:i');

        $tmpFile = tempnam(sys_get_temp_dir(), 'spl_procure_');
        $zip = new ZipArchive();

        if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file temporary XLSX.');
        }

        // Definisi Sheets Terpisah Sesuai Kategori
        $sheets = [
            ['id' => 1, 'name' => '1. Rekap Semua Barang', 'type' => 'all_goods'],
            ['id' => 2, 'name' => '2. Parsel Harian', 'type' => 'parsel_harian'],
            ['id' => 3, 'name' => '3. Parsel Mingguan', 'type' => 'parsel_mingguan'],
            ['id' => 4, 'name' => '4. Paket Eceran', 'type' => 'ecer'],
            ['id' => 5, 'name' => '5. Hadiah Tabungan', 'type' => 'tabungan_bonus'],
            ['id' => 6, 'name' => '6. Kemasan & Wadah', 'type' => 'packaging'],
            ['id' => 7, 'name' => '7. Rekapan Paket', 'type' => 'packages'],
            ['id' => 8, 'name' => '8. Uang Tunai', 'type' => 'cash'],
            ['id' => 9, 'name' => '9. Ringkasan Anggaran', 'type' => 'summary'],
        ];

        // 1. [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', $this->buildContentTypesXml($sheets));

        // 2. _rels/.rels
        $zip->addFromString('_rels/.rels', $this->buildRootRelsXml());

        // 3. xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelsXml($sheets));

        // 4. xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbookXml($sheets));

        // 5. xl/styles.xml (Palet Warna SPL Pink & Siap Cetak)
        $zip->addFromString('xl/styles.xml', $this->buildStylesXml());

        // 6. Buat masing-masing Sheet XML
        foreach ($sheets as $s) {
            $path = "xl/worksheets/sheet{$s['id']}.xml";
            $sheetXml = match($s['type']) {
                'all_goods' => $this->buildGoodsSheetXml(
                    $plannerData['all_goods_items'] ?? $plannerData['items'] ?? [],
                    '1. TOTAL REKAPITULASI KULAKAN GROSIR SELURUH BARANG (SEMUA PAKET)',
                    $appName, $programPeriod, $printedDate, $scope, true
                ),
                'parsel_harian' => $this->buildGoodsSheetXml(
                    $plannerData['parsel_harian_items'] ?? [],
                    '2. DAFTAR BELANJA BARANG: PAKET PARSEL HARIAN',
                    $appName, $programPeriod, $printedDate, $scope, false
                ),
                'parsel_mingguan' => $this->buildGoodsSheetXml(
                    $plannerData['parsel_mingguan_items'] ?? [],
                    '3. DAFTAR BELANJA BARANG: PAKET PARSEL MINGGUAN',
                    $appName, $programPeriod, $printedDate, $scope, false
                ),
                'ecer' => $this->buildGoodsSheetXml(
                    $plannerData['ecer_items'] ?? [],
                    '4. DAFTAR BELANJA BARANG: PAKET ECERAN / BEBAS',
                    $appName, $programPeriod, $printedDate, $scope, false
                ),
                'tabungan_bonus' => $this->buildGoodsSheetXml(
                    $plannerData['tabungan_bonus_items'] ?? [],
                    '5. DAFTAR BELANJA HADIAH BONUS TABUNGAN LEBARAN',
                    $appName, $programPeriod, $printedDate, $scope, false
                ),
                'packaging' => $this->buildPackagingSheetXml($plannerData, $appName, $programPeriod, $printedDate, $scope),
                'packages' => $this->buildPackagesSheetXml($plannerData, $appName, $programPeriod, $printedDate, $scope),
                'cash' => $this->buildCashSheetXml($plannerData, $appName, $programPeriod, $printedDate, $scope),
                'summary' => $this->buildSummarySheetXml($plannerData, $appName, $programPeriod, $printedDate, $scope),
            };
            $zip->addFromString($path, $sheetXml);
        }

        $zip->close();

        $scopeSuffix = $scope === 'all' ? 'Semua_Konsumen' : 'Lolos_Titik_Aman';
        $cleanAppName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $appName);
        $downloadName = "PO_Supplier_Rekap_Belanja_{$scopeSuffix}_{$cleanAppName}_" . date('Ymd_His') . ".xlsx";

        $content = file_get_contents($tmpFile);
        @unlink($tmpFile);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$downloadName}\"",
            'Content-Length' => strlen($content),
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    private function buildContentTypesXml(array $sheets): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n";
        $xml .= '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n";
        $xml .= '  <Default Extension="xml" ContentType="application/xml"/>' . "\n";
        $xml .= '  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n";
        $xml .= '  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . "\n";

        foreach ($sheets as $s) {
            $xml .= "  <Override PartName=\"/xl/worksheets/sheet{$s['id']}.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>\n";
        }

        $xml .= '</Types>';
        return $xml;
    }

    private function buildRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n" .
            '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' . "\n" .
            '</Relationships>';
    }

    private function buildWorkbookRelsXml(array $sheets): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n";
        $xml .= '  <Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' . "\n";

        foreach ($sheets as $s) {
            $xml .= "  <Relationship Id=\"rId{$s['id']}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet{$s['id']}.xml\"/>\n";
        }

        $xml .= '</Relationships>';
        return $xml;
    }

    private function buildWorkbookXml(array $sheets): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        $xml .= '  <sheets>' . "\n";

        foreach ($sheets as $s) {
            $safeName = htmlspecialchars($s['name'], ENT_XML1, 'UTF-8');
            $xml .= "    <sheet name=\"{$safeName}\" sheetId=\"{$s['id']}\" r:id=\"rId{$s['id']}\"/>\n";
        }

        $xml .= '  </sheets>' . "\n";
        $xml .= '</workbook>';
        return $xml;
    }

    private function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n" .
'  <numFmts count="2">' . "\n" .
'    <numFmt numFmtId="164" formatCode="#,##0"/>' . "\n" .
'    <numFmt numFmtId="165" formatCode="&quot;Rp &quot;#,##0"/>' . "\n" .
'  </numFmts>' . "\n" .
'  <fonts count="7">' . "\n" .
'    <font><sz val="10"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="14"/><color rgb="FF831843"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="11"/><color rgb="FF0F172A"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="10"/><color rgb="FF0F172A"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><sz val="9"/><color rgb="FF64748B"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="10"/><color rgb="FF831843"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'  </fonts>' . "\n" .
'  <fills count="6">' . "\n" .
'    <fill><patternFill patternType="none"/></fill>' . "\n" .
'    <fill><patternFill patternType="gray125"/></fill>' . "\n" .
'    <fill><patternFill patternType="solid"><fgColor rgb="FFBE185D"/><bgColor indexed="64"/></patternFill></fill>' . "\n" .
'    <fill><patternFill patternType="solid"><fgColor rgb="FFFCE7F1"/><bgColor indexed="64"/></patternFill></fill>' . "\n" .
'    <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/><bgColor indexed="64"/></patternFill></fill>' . "\n" .
'    <fill><patternFill patternType="solid"><fgColor rgb="FF7C3AED"/><bgColor indexed="64"/></patternFill></fill>' . "\n" .
'  </fills>' . "\n" .
'  <borders count="2">' . "\n" .
'    <border><left/><right/><top/><bottom/><diagonal/></border>' . "\n" .
'    <border>' . "\n" .
'      <left style="thin"><color rgb="FFCBD5E1"/></left>' . "\n" .
'      <right style="thin"><color rgb="FFCBD5E1"/></right>' . "\n" .
'      <top style="thin"><color rgb="FFCBD5E1"/></top>' . "\n" .
'      <bottom style="thin"><color rgb="FFCBD5E1"/></bottom>' . "\n" .
'      <diagonal/>' . "\n" .
'    </border>' . "\n" .
'  </borders>' . "\n" .
'  <cellStyleXfs count="1">' . "\n" .
'    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' . "\n" .
'  </cellStyleXfs>' . "\n" .
'  <cellXfs count="12">' . "\n" .
'    <!-- 0: normal -->' . "\n" .
'    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' . "\n" .
'    <!-- 1: title -->' . "\n" .
'    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>' . "\n" .
'    <!-- 2: subtitle -->' . "\n" .
'    <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>' . "\n" .
'    <!-- 3: meta info -->' . "\n" .
'    <xf numFmtId="0" fontId="5" fillId="0" borderId="0" xfId="0" applyFont="1"/>' . "\n" .
'    <!-- 4: table header berry pink (bold white text, berry pink fill, border, center) -->' . "\n" .
'    <xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' . "\n" .
'      <alignment horizontal="center" vertical="center" wrapText="1"/>' . "\n" .
'    </xf>' . "\n" .
'    <!-- 5: data text left (border) -->' . "\n" .
'    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">' . "\n" .
'      <alignment horizontal="left" vertical="center"/>' . "\n" .
'    </xf>' . "\n" .
'    <!-- 6: data text center (border) -->' . "\n" .
'    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">' . "\n" .
'      <alignment horizontal="center" vertical="center"/>' . "\n" .
'    </xf>' . "\n" .
'    <!-- 7: data integer right (border, numFmt 164) -->' . "\n" .
'    <xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1">' . "\n" .
'      <alignment horizontal="right" vertical="center"/>' . "\n" .
'    </xf>' . "\n" .
'    <!-- 8: data currency right (border, numFmt 165) -->' . "\n" .
'    <xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1">' . "\n" .
'      <alignment horizontal="right" vertical="center"/>' . "\n" .
'    </xf>' . "\n" .
'    <!-- 9: total summary row bold (soft pink fill, border, right, numFmt 165) -->' . "\n" .
'    <xf numFmtId="165" fontId="4" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' . "\n" .
'      <alignment horizontal="right" vertical="center"/>' . "\n" .
'    </xf>' . "\n" .
'    <!-- 10: header purple (bold white, purple fill, border, center) -->' . "\n" .
'    <xf numFmtId="0" fontId="3" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' . "\n" .
'      <alignment horizontal="center" vertical="center" wrapText="1"/>' . "\n" .
'    </xf>' . "\n" .
'    <!-- 11: total summary row bold integer (soft pink fill, border, center, numFmt 164) -->' . "\n" .
'    <xf numFmtId="164" fontId="4" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' . "\n" .
'      <alignment horizontal="center" vertical="center"/>' . "\n" .
'    </xf>' . "\n" .
'  </cellXfs>' . "\n" .
'  <cellStyles count="1">' . "\n" .
'    <cellStyle name="Normal" xfId="0" builtinId="0"/>' . "\n" .
'  </cellStyles>' . "\n" .
'</styleSheet>';
    }

    /**
     * Builder XML untuk Sheet Belanja Barang (Bisa untuk Rekap Semua Barang, Parsel Harian, Parsel Mingguan, Eceran, Hadiah Tabungan)
     */
    private function buildGoodsSheetXml(array $items, string $sheetTitle, string $appName, string $period, string $date, string $scope, bool $showBreakdown = false): string
    {
        $scopeLabel = $scope === 'all' ? 'Seluruh Pesanan Konsumen Aktif (PO ke Supplier)' : 'Hanya Konsumen Lolos Titik Aman';

        if ($showBreakdown) {
            $colWidths = [
                1 => 6,   // A: No
                2 => 16,  // B: Kode Barang
                3 => 32,  // C: Nama Barang Pokok
                4 => 18,  // D: Kategori
                5 => 18,  // E: Total Satuan Dasar
                6 => 14,  // F: Satuan Dasar
                7 => 14,  // G: Isi per Dus
                8 => 26,  // H: Rekomendasi Kulakan Grosir
                9 => 16,  // I: Pembulatan Dus
                10 => 14, // J: Satuan Grosir
                11 => 14, // K: Parsel Harian
                12 => 14, // L: Parsel Mingguan
                13 => 14, // M: Dari Ecer
                14 => 16, // N: Hadiah Tabungan
                15 => 18, // O: Harga Modal
                16 => 22, // P: Total Estimasi Modal
            ];
            $headers = [
                'A' => 'No',
                'B' => 'Kode Barang',
                'C' => 'Nama Barang',
                'D' => 'Kategori',
                'E' => 'Total Pesanan (Satuan)',
                'F' => 'Satuan Dasar',
                'G' => 'Isi per Dus',
                'H' => 'Kebutuhan Kulakan Grosir',
                'I' => 'Genap (Dus)',
                'J' => 'Satuan Grosir',
                'K' => 'Parsel Harian',
                'L' => 'Parsel Mingguan',
                'M' => 'Dari Ecer',
                'N' => 'Hadiah Tabungan',
                'O' => 'Harga Modal / Unit (Rp)',
                'P' => 'Total Estimasi Modal (Rp)',
            ];
            $lastCol = 'P';
        } else {
            $colWidths = [
                1 => 6,   // A: No
                2 => 16,  // B: Kode Barang
                3 => 32,  // C: Nama Barang Pokok
                4 => 18,  // D: Kategori
                5 => 18,  // E: Total Satuan Dasar
                6 => 14,  // F: Satuan Dasar
                7 => 14,  // G: Isi per Dus
                8 => 26,  // H: Rekomendasi Kulakan Grosir
                9 => 18,  // I: Pembulatan Dus
                10 => 14, // J: Satuan Grosir
                11 => 18, // K: Harga Modal
                12 => 22, // L: Total Estimasi Modal
            ];
            $headers = [
                'A' => 'No',
                'B' => 'Kode Barang',
                'C' => 'Nama Barang',
                'D' => 'Kategori',
                'E' => 'Total Pesanan (Satuan)',
                'F' => 'Satuan Dasar',
                'G' => 'Isi per Dus',
                'H' => 'Kebutuhan Kulakan Grosir',
                'I' => 'Genap (Dus)',
                'J' => 'Satuan Grosir',
                'K' => 'Harga Modal / Unit (Rp)',
                'L' => 'Total Estimasi Modal (Rp)',
            ];
            $lastCol = 'L';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '  <sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>' . "\n";
        $xml .= '  <sheetFormatPr defaultRowHeight="20"/>' . "\n";
        $xml .= '  <cols>' . "\n";
        foreach ($colWidths as $col => $w) {
            $xml .= "    <col min=\"{$col}\" max=\"{$col}\" width=\"{$w}\" customWidth=\"1\"/>\n";
        }
        $xml .= '  </cols>' . "\n";
        $xml .= '  <sheetData>' . "\n";

        // Row 1: Title
        $r = 1;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, strtoupper($appName), 1);
        $xml .= "    </row>\n";

        // Row 2: Subtitle
        $r = 2;
        $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
        $xml .= $this->cellStr('A', $r, $sheetTitle, 2);
        $xml .= "    </row>\n";

        // Row 3: Meta Info
        $r = 3;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $xml .= $this->cellStr('A', $r, "Cakupan: {$scopeLabel} | Periode: {$period} | Dicetak: {$date} | Total: " . count($items) . " Macam Barang", 3);
        $xml .= "    </row>\n";

        // Row 4: Empty row
        $r = 4;
        $xml .= "    <row r=\"{$r}\" ht=\"10\"/>\n";

        // Row 5: Table Header
        $r = 5;
        $xml .= "    <row r=\"{$r}\" ht=\"28\">\n";
        foreach ($headers as $col => $text) {
            $xml .= $this->cellStr($col, $r, $text, 4);
        }
        $xml .= "    </row>\n";

        $totPieces = 0;
        $totCost = 0;
        $startDataRow = 6;

        foreach ($items as $idx => $row) {
            $r++;
            $totPieces += $row['total_base_qty'];
            $totCost += $row['estimated_cost'];

            $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
            $xml .= $this->cellNum('A', $r, $idx + 1, 6);
            $xml .= $this->cellStr('B', $r, $row['item_code'], 6);
            $xml .= $this->cellStr('C', $r, $row['item_name'], 5);
            $xml .= $this->cellStr('D', $r, $row['category'], 6);
            $xml .= $this->cellNum('E', $r, $row['total_base_qty'], 7);
            $xml .= $this->cellStr('F', $r, $row['base_unit'], 6);
            $xml .= $this->cellNum('G', $r, (int)$row['conversion_rate'], 6);
            $xml .= $this->cellStr('H', $r, $row['wholesale_text'], 5);
            $xml .= $this->cellNum('I', $r, $row['wholesale_ceil'], 7);
            $xml .= $this->cellStr('J', $r, $row['wholesale_unit'] ?: 'Dus', 6);

            if ($showBreakdown) {
                $xml .= $this->cellNum('K', $r, $row['from_parsel_harian'] ?? 0, 7);
                $xml .= $this->cellNum('L', $r, $row['from_parsel_mingguan'] ?? 0, 7);
                $xml .= $this->cellNum('M', $r, $row['from_ecer'] ?? 0, 7);
                $xml .= $this->cellNum('N', $r, $row['from_tabungan_bonus'] ?? 0, 7);
                $xml .= $this->cellNum('O', $r, $row['cost_price'], 8);
                $xml .= $this->cellNum('P', $r, $row['estimated_cost'], 8);
            } else {
                $xml .= $this->cellNum('K', $r, $row['cost_price'], 8);
                $xml .= $this->cellNum('L', $r, $row['estimated_cost'], 8);
            }
            $xml .= "    </row>\n";
        }

        $lastDataRow = $r;

        // Total Row
        $r++;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, '', 9);
        $xml .= $this->cellStr('B', $r, 'TOTAL KESELURUHAN', 9);
        $xml .= $this->cellStr('C', $r, count($items) . ' Macam Barang', 9);
        $xml .= $this->cellStr('D', $r, '', 9);
        $xml .= $this->cellNum('E', $r, $totPieces, 9);
        $xml .= $this->cellStr('F', $r, 'Satuan', 9);
        $xml .= $this->cellStr('G', $r, '', 9);
        $xml .= $this->cellStr('H', $r, '', 9);
        $xml .= $this->cellStr('I', $r, '', 9);
        $xml .= $this->cellStr('J', $r, '', 9);

        if ($showBreakdown) {
            $xml .= $this->cellStr('K', $r, '', 9);
            $xml .= $this->cellStr('L', $r, '', 9);
            $xml .= $this->cellStr('M', $r, '', 9);
            $xml .= $this->cellStr('N', $r, '', 9);
            $xml .= $this->cellStr('O', $r, '', 9);
            $xml .= $this->cellNum('P', $r, $totCost, 9);
        } else {
            $xml .= $this->cellStr('K', $r, '', 9);
            $xml .= $this->cellNum('L', $r, $totCost, 9);
        }
        $xml .= "    </row>\n";

        $xml .= '  </sheetData>' . "\n";
        if ($lastDataRow >= 6) {
            $xml .= "  <autoFilter ref=\"A5:{$lastCol}{$lastDataRow}\"/>\n";
        }
        $xml .= '  <printOptions gridLines="1"/>' . "\n";
        $xml .= '  <pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $xml .= '  <pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>' . "\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    /**
     * Sheet 2: Belanja Kemasan / Wadah (Koper, Box Kontainer, Keranjang, Toples, Ember, dll.)
     */
    private function buildPackagingSheetXml(array $data, string $appName, string $period, string $date, string $scope): string
    {
        $packagings = $data['packaging_items'];
        $scopeLabel = $scope === 'all' ? 'Seluruh Pesanan Konsumen Aktif (PO ke Supplier)' : 'Hanya Konsumen Lolos Titik Aman';

        $colWidths = [
            1 => 6,   // A: No
            2 => 18,  // B: Kode Kemasan
            3 => 32,  // C: Nama Kemasan / Wadah
            4 => 18,  // D: Total Unit Dibeli
            5 => 20,  // E: Harga Kulak / Unit
            6 => 24,  // F: Total Biaya Kulak
            7 => 45,  // G: Rincian Paket Pemesan
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '  <sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>' . "\n";
        $xml .= '  <sheetFormatPr defaultRowHeight="20"/>' . "\n";
        $xml .= '  <cols>' . "\n";
        foreach ($colWidths as $col => $w) {
            $xml .= "    <col min=\"{$col}\" max=\"{$col}\" width=\"{$w}\" customWidth=\"1\"/>\n";
        }
        $xml .= '  </cols>' . "\n";
        $xml .= '  <sheetData>' . "\n";

        // Row 1: Title
        $r = 1;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, strtoupper($appName), 1);
        $xml .= "    </row>\n";

        // Row 2: Subtitle
        $r = 2;
        $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
        $xml .= $this->cellStr('A', $r, '6. REKAPITULASI BELANJA KEMASAN & WADAH PARSEL (KOPER, BOX, KERANJANG, TOPLES, DLL.)', 2);
        $xml .= "    </row>\n";

        // Row 3: Meta Info
        $r = 3;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $xml .= $this->cellStr('A', $r, "Cakupan: {$scopeLabel} | Periode: {$period} | Dicetak: {$date} | Total: " . count($packagings) . " Tipe Wadah", 3);
        $xml .= "    </row>\n";

        // Row 4: Empty row
        $r = 4;
        $xml .= "    <row r=\"{$r}\" ht=\"10\"/>\n";

        // Row 5: Table Header
        $r = 5;
        $headers = [
            'A' => 'No',
            'B' => 'Kode Kemasan',
            'C' => 'Nama Kemasan / Wadah',
            'D' => 'Total Unit Dibeli',
            'E' => 'Harga Modal / Unit (Rp)',
            'F' => 'Total Biaya Kulak (Rp)',
            'G' => 'Daftar Paket Pemesan Wadah',
        ];
        $xml .= "    <row r=\"{$r}\" ht=\"28\">\n";
        foreach ($headers as $col => $text) {
            $xml .= $this->cellStr($col, $r, $text, 10);
        }
        $xml .= "    </row>\n";

        $totUnits = 0;
        $totCost = 0;

        foreach ($packagings as $idx => $p) {
            $r++;
            $totUnits += $p['total_qty'];
            $totCost += $p['total_cost'];

            $packageNames = [];
            foreach ($p['recipients'] ?? [] as $rc) {
                if (!empty($rc['package_name'])) {
                    $packageNames[$rc['package_name']] = ($packageNames[$rc['package_name']] ?? 0) + 1;
                }
            }
            $pkgStr = '';
            foreach ($packageNames as $pName => $cnt) {
                $pkgStr .= ($pkgStr ? ', ' : '') . "{$pName} ({$cnt}x)";
            }

            $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
            $xml .= $this->cellNum('A', $r, $idx + 1, 6);
            $xml .= $this->cellStr('B', $r, $p['code'] ?: '-', 6);
            $xml .= $this->cellStr('C', $r, $p['name'], 5);
            $xml .= $this->cellNum('D', $r, $p['total_qty'], 7);
            $xml .= $this->cellNum('E', $r, $p['cost_price'], 8);
            $xml .= $this->cellNum('F', $r, $p['total_cost'], 8);
            $xml .= $this->cellStr('G', $r, $pkgStr ?: '-', 5);
            $xml .= "    </row>\n";
        }

        $lastDataRow = $r;

        // Total Row
        $r++;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, '', 9);
        $xml .= $this->cellStr('B', $r, 'TOTAL KEMASAN / WADAH', 9);
        $xml .= $this->cellStr('C', $r, count($packagings) . ' Model Wadah', 9);
        $xml .= $this->cellNum('D', $r, $totUnits, 9);
        $xml .= $this->cellStr('E', $r, '', 9);
        $xml .= $this->cellNum('F', $r, $totCost, 9);
        $xml .= $this->cellStr('G', $r, '', 9);
        $xml .= "    </row>\n";

        $xml .= '  </sheetData>' . "\n";
        if ($lastDataRow >= 6) {
            $xml .= "  <autoFilter ref=\"A5:G{$lastDataRow}\"/>\n";
        }
        $xml .= '  <printOptions gridLines="1"/>' . "\n";
        $xml .= '  <pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $xml .= '  <pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>' . "\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    /**
     * Sheet 3: Belanja Hadiah Bonus Paket
     */
    private function buildBonusSheetXml(array $data, string $appName, string $period, string $date, string $scope): string
    {
        $bonuses = $data['bonus_items'];
        $scopeLabel = $scope === 'all' ? 'Seluruh Pesanan Konsumen Aktif (PO ke Supplier)' : 'Hanya Konsumen Lolos Titik Aman';

        $colWidths = [
            1 => 6,   // A: No
            2 => 16,  // B: Kode Item
            3 => 32,  // C: Nama Hadiah Bonus
            4 => 18,  // D: Kategori
            5 => 16,  // E: Total Hadiah (Pcs)
            6 => 14,  // F: Satuan
            7 => 14,  // G: Isi per Dus
            8 => 24,  // H: Rekomendasi Grosir
            9 => 16,  // I: Genap (Dus)
            10 => 18, // J: Harga Modal
            11 => 22, // K: Total Estimasi Modal
            12 => 36, // L: Paket Pemberi Hadiah
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '  <sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>' . "\n";
        $xml .= '  <sheetFormatPr defaultRowHeight="20"/>' . "\n";
        $xml .= '  <cols>' . "\n";
        foreach ($colWidths as $col => $w) {
            $xml .= "    <col min=\"{$col}\" max=\"{$col}\" width=\"{$w}\" customWidth=\"1\"/>\n";
        }
        $xml .= '  </cols>' . "\n";
        $xml .= '  <sheetData>' . "\n";

        // Row 1: Title
        $r = 1;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, strtoupper($appName), 1);
        $xml .= "    </row>\n";

        // Row 2: Subtitle
        $r = 2;
        $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
        $xml .= $this->cellStr('A', $r, '3. REKAPITULASI BELANJA HADIAH BONUS PAKET (TABUNGAN & PARSEL)', 2);
        $xml .= "    </row>\n";

        // Row 3: Meta Info
        $r = 3;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $xml .= $this->cellStr('A', $r, "Cakupan: {$scopeLabel} | Periode: {$period} | Dicetak: {$date} | Total: " . count($bonuses) . " Macam Hadiah", 3);
        $xml .= "    </row>\n";

        // Row 4: Empty row
        $r = 4;
        $xml .= "    <row r=\"{$r}\" ht=\"10\"/>\n";

        // Row 5: Table Header
        $r = 5;
        $headers = [
            'A' => 'No',
            'B' => 'Kode Item',
            'C' => 'Nama Barang Hadiah Bonus',
            'D' => 'Kategori',
            'E' => 'Total Hadiah (Pcs)',
            'F' => 'Satuan',
            'G' => 'Isi per Dus',
            'H' => 'Kebutuhan Grosir',
            'I' => 'Genap (Dus)',
            'J' => 'Harga Modal / Unit (Rp)',
            'K' => 'Total Estimasi Modal (Rp)',
            'L' => 'Paket yang Memberikan Hadiah',
        ];
        $xml .= "    <row r=\"{$r}\" ht=\"28\">\n";
        foreach ($headers as $col => $text) {
            $xml .= $this->cellStr($col, $r, $text, 4);
        }
        $xml .= "    </row>\n";

        $totPieces = 0;
        $totCost = 0;

        foreach ($bonuses as $idx => $b) {
            $r++;
            $totPieces += $b['total_base_qty'];
            $totCost += $b['estimated_cost'];

            $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
            $xml .= $this->cellNum('A', $r, $idx + 1, 6);
            $xml .= $this->cellStr('B', $r, $b['item_code'], 6);
            $xml .= $this->cellStr('C', $r, $b['item_name'], 5);
            $xml .= $this->cellStr('D', $r, $b['category'], 6);
            $xml .= $this->cellNum('E', $r, $b['total_base_qty'], 7);
            $xml .= $this->cellStr('F', $r, $b['base_unit'], 6);
            $xml .= $this->cellNum('G', $r, (int)$b['conversion_rate'], 6);
            $xml .= $this->cellStr('H', $r, $b['wholesale_text'], 5);
            $xml .= $this->cellNum('I', $r, $b['wholesale_ceil'], 7);
            $xml .= $this->cellNum('J', $r, $b['cost_price'], 8);
            $xml .= $this->cellNum('K', $r, $b['estimated_cost'], 8);
            $xml .= $this->cellStr('L', $r, implode(', ', $b['packages_list']), 5);
            $xml .= "    </row>\n";
        }

        $lastDataRow = $r;

        // Total Row
        $r++;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, '', 9);
        $xml .= $this->cellStr('B', $r, 'TOTAL HADIAH BONUS PAKET', 9);
        $xml .= $this->cellStr('C', $r, count($bonuses) . ' Macam Hadiah', 9);
        $xml .= $this->cellStr('D', $r, '', 9);
        $xml .= $this->cellNum('E', $r, $totPieces, 9);
        $xml .= $this->cellStr('F', $r, 'Pcs', 9);
        $xml .= $this->cellStr('G', $r, '', 9);
        $xml .= $this->cellStr('H', $r, '', 9);
        $xml .= $this->cellStr('I', $r, '', 9);
        $xml .= $this->cellStr('J', $r, '', 9);
        $xml .= $this->cellNum('K', $r, $totCost, 9);
        $xml .= $this->cellStr('L', $r, '', 9);
        $xml .= "    </row>\n";

        $xml .= '  </sheetData>' . "\n";
        if ($lastDataRow >= 6) {
            $xml .= "  <autoFilter ref=\"A5:L{$lastDataRow}\"/>\n";
        }
        $xml .= '  <printOptions gridLines="1"/>' . "\n";
        $xml .= '  <pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $xml .= '  <pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>' . "\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    /**
     * Sheet 4: Rekapan Jumlah Paket yang Harus Disiapkan
     */
    private function buildPackagesSheetXml(array $data, string $appName, string $period, string $date, string $scope): string
    {
        $packages = $data['packages_to_prepare'];
        $scopeLabel = $scope === 'all' ? 'Seluruh Pesanan Konsumen Aktif (PO ke Supplier)' : 'Hanya Konsumen Lolos Titik Aman';

        $colWidths = [
            1 => 6,   // A: No
            2 => 16,  // B: Kode Paket
            3 => 30,  // C: Nama Paket
            4 => 16,  // D: Jenis Paket
            5 => 18,  // E: Skema & Setoran
            6 => 16,  // F: Total Pesanan
            7 => 14,  // G: Pending
            8 => 14,  // H: Diserahkan
            9 => 22,  // I: Total Uang Tunai Disiapkan
            10 => 22, // J: Total Target Nominal
            11 => 40, // K: Komposisi Barang & Hadiah
            12 => 26, // L: Kemasan Terpilih
            13 => 30, // M: Sebaran Agen
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '  <sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>' . "\n";
        $xml .= '  <sheetFormatPr defaultRowHeight="20"/>' . "\n";
        $xml .= '  <cols>' . "\n";
        foreach ($colWidths as $col => $w) {
            $xml .= "    <col min=\"{$col}\" max=\"{$col}\" width=\"{$w}\" customWidth=\"1\"/>\n";
        }
        $xml .= '  </cols>' . "\n";
        $xml .= '  <sheetData>' . "\n";

        // Row 1: Title
        $r = 1;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, strtoupper($appName), 1);
        $xml .= "    </row>\n";

        // Row 2: Subtitle
        $r = 2;
        $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
        $xml .= $this->cellStr('A', $r, '7. REKAPITULASI JUMLAH PAKET YANG HARUS DISIAPKAN (PERMINTAAN KONSUMEN)', 2);
        $xml .= "    </row>\n";

        // Row 3: Meta Info
        $r = 3;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $xml .= $this->cellStr('A', $r, "Cakupan: {$scopeLabel} | Periode: {$period} | Dicetak: {$date} | Total: " . count($packages) . " Paket Terdaftar", 3);
        $xml .= "    </row>\n";

        // Row 4: Empty row
        $r = 4;
        $xml .= "    <row r=\"{$r}\" ht=\"10\"/>\n";

        // Row 5: Table Header
        $r = 5;
        $headers = [
            'A' => 'No',
            'B' => 'Kode Paket',
            'C' => 'Nama Paket',
            'D' => 'Jenis Paket',
            'E' => 'Skema Tagihan',
            'F' => 'Total Pesanan',
            'G' => 'Pending',
            'H' => 'Diserahkan',
            'I' => 'Uang Tunai Disiapkan (Rp)',
            'J' => 'Total Target Nominal (Rp)',
            'K' => 'Komposisi Barang & Hadiah Total',
            'L' => 'Kemasan Wadah Terpilih',
            'M' => 'Sebaran Agen Pemesan',
        ];
        $xml .= "    <row r=\"{$r}\" ht=\"28\">\n";
        foreach ($headers as $col => $text) {
            $xml .= $this->cellStr($col, $r, $text, 4);
        }
        $xml .= "    </row>\n";

        $totOrders = 0;
        $totPending = 0;
        $totFulfilled = 0;
        $totCash = 0;
        $totTarget = 0;

        foreach ($packages as $idx => $p) {
            $r++;
            $totOrders += $p['total_orders'];
            $totPending += $p['pending_orders'];
            $totFulfilled += $p['fulfilled_orders'];
            $totCash += $p['total_cash_needed'];
            $totTarget += $p['total_target_amount'];

            // Komposisi barang
            $itemsSummary = [];
            foreach ($p['total_goods_needed'] ?? [] as $g) {
                $itemsSummary[] = "{$g['item_name']} ({$g['total_qty']} {$g['unit']})";
            }
            foreach ($p['bonuses_needed'] ?? [] as $b) {
                $itemsSummary[] = "[Bonus] {$b['item_name']} ({$b['total_qty']} {$b['unit']})";
            }
            $itemsStr = implode(', ', array_slice($itemsSummary, 0, 8));
            if (count($itemsSummary) > 8) {
                $itemsStr .= ' ... (+ ' . (count($itemsSummary) - 8) . ' barang lainnya)';
            }

            // Kemasan
            $kmsSummary = [];
            foreach ($p['packagings_needed'] ?? [] as $kms) {
                $kmsSummary[] = "{$kms['name']}: {$kms['qty']} Unit";
            }
            $kmsStr = implode(', ', $kmsSummary);

            // Agen
            $agentsSummary = [];
            foreach ($p['agents'] ?? [] as $ag) {
                $agentsSummary[] = "{$ag['name']} ({$ag['count']})";
            }
            $agentsStr = implode(', ', $agentsSummary);

            $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
            $xml .= $this->cellNum('A', $r, $idx + 1, 6);
            $xml .= $this->cellStr('B', $r, $p['package_code'], 6);
            $xml .= $this->cellStr('C', $r, $p['package_name'], 5);
            $xml .= $this->cellStr('D', $r, $p['package_type_label'], 6);
            $xml .= $this->cellStr('E', $r, $p['installment_label'] . ' (' . $p['installment_count'] . 'x)', 6);
            $xml .= $this->cellNum('F', $r, $p['total_orders'], 7);
            $xml .= $this->cellNum('G', $r, $p['pending_orders'], 7);
            $xml .= $this->cellNum('H', $r, $p['fulfilled_orders'], 7);
            $xml .= $this->cellNum('I', $r, $p['total_cash_needed'], 8);
            $xml .= $this->cellNum('J', $r, $p['total_target_amount'], 8);
            $xml .= $this->cellStr('K', $r, $itemsStr ?: '-', 5);
            $xml .= $this->cellStr('L', $r, $kmsStr ?: 'Standar', 5);
            $xml .= $this->cellStr('M', $r, $agentsStr ?: '-', 5);
            $xml .= "    </row>\n";
        }

        $lastDataRow = $r;

        // Total Row
        $r++;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, '', 9);
        $xml .= $this->cellStr('B', $r, 'TOTAL KESELURUHAN PAKET', 9);
        $xml .= $this->cellStr('C', $r, count($packages) . ' Master Paket', 9);
        $xml .= $this->cellStr('D', $r, '', 9);
        $xml .= $this->cellStr('E', $r, '', 9);
        $xml .= $this->cellNum('F', $r, $totOrders, 9);
        $xml .= $this->cellNum('G', $r, $totPending, 9);
        $xml .= $this->cellNum('H', $r, $totFulfilled, 9);
        $xml .= $this->cellNum('I', $r, $totCash, 9);
        $xml .= $this->cellNum('J', $r, $totTarget, 9);
        $xml .= $this->cellStr('K', $r, '', 9);
        $xml .= $this->cellStr('L', $r, '', 9);
        $xml .= $this->cellStr('M', $r, '', 9);
        $xml .= "    </row>\n";

        $xml .= '  </sheetData>' . "\n";
        if ($lastDataRow >= 6) {
            $xml .= "  <autoFilter ref=\"A5:M{$lastDataRow}\"/>\n";
        }
        $xml .= '  <printOptions gridLines="1"/>' . "\n";
        $xml .= '  <pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $xml .= '  <pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>' . "\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    /**
     * Sheet 8: Daftar Penyiapan Uang Tunai Konsumen (Tabungan & Parsel)
     */
    private function buildCashSheetXml(array $data, string $appName, string $period, string $date, string $scope): string
    {
        $cashItems = $data['cash_items'] ?? $data['cash_requirements'] ?? [];
        $scopeLabel = $scope === 'all' || $scope === 'raw_all' ? 'Seluruh Pesanan Konsumen Aktif (PO ke Supplier)' : 'Hanya Konsumen Lolos Titik Aman / Ditetapkan Tutup Buku';

        $colWidths = [
            1 => 6,   // A: No
            2 => 14,  // B: Kode Konsumen
            3 => 26,  // C: Nama Konsumen
            4 => 20,  // D: Agen
            5 => 26,  // E: Paket
            6 => 18,  // F: Skema / Durasi
            7 => 30,  // G: Rincian Uang Tunai
            8 => 22,  // H: Nominal Uang Tunai (Rp)
            9 => 20,  // I: Total Terbayar (Rp)
            10 => 14, // J: Progress
            11 => 18, // K: No. HP
            12 => 32, // L: Alamat
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '  <sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>' . "\n";
        $xml .= '  <sheetFormatPr defaultRowHeight="20"/>' . "\n";
        $xml .= '  <cols>' . "\n";
        foreach ($colWidths as $col => $w) {
            $xml .= "    <col min=\"{$col}\" max=\"{$col}\" width=\"{$w}\" customWidth=\"1\"/>\n";
        }
        $xml .= '  </cols>' . "\n";
        $xml .= '  <sheetData>' . "\n";

        // Row 1: Title
        $r = 1;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, strtoupper($appName), 1);
        $xml .= "    </row>\n";

        // Row 2: Subtitle
        $r = 2;
        $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
        $xml .= $this->cellStr('A', $r, '8. DAFTAR PENYIAPAN UANG TUNAI KONSUMEN (TABUNGAN & PARSEL)', 2);
        $xml .= "    </row>\n";

        // Row 3: Meta Info
        $r = 3;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $xml .= $this->cellStr('A', $r, "Cakupan: {$scopeLabel} | Periode: {$period} | Dicetak: {$date} | Total: " . count($cashItems) . " Penerima Uang Tunai", 3);
        $xml .= "    </row>\n";

        // Row 4: Empty row
        $r = 4;
        $xml .= "    <row r=\"{$r}\" ht=\"10\"/>\n";

        // Row 5: Table Header
        $r = 5;
        $headers = [
            'A' => 'No',
            'B' => 'Kode Konsumen',
            'C' => 'Nama Konsumen',
            'D' => 'Agen Penanggungjawab',
            'E' => 'Paket Terdaftar',
            'F' => 'Skema Tagihan',
            'G' => 'Rincian Uang Tunai',
            'H' => 'Nominal Uang Tunai (Rp)',
            'I' => 'Total Terbayar (Rp)',
            'J' => 'Progress Setoran',
            'K' => 'No. HP',
            'L' => 'Alamat Konsumen',
        ];
        $xml .= "    <row r=\"{$r}\" ht=\"28\">\n";
        foreach ($headers as $col => $text) {
            $xml .= $this->cellStr($col, $r, $text, 4);
        }
        $xml .= "    </row>\n";

        $totCash = 0;
        $totPaid = 0;

        foreach ($cashItems as $idx => $c) {
            $r++;
            $cashAmount = (float)($c['cash_amount'] ?? 0);
            $totalPaid = (float)($c['total_paid'] ?? 0);
            $totCash += $cashAmount;
            $totPaid += $totalPaid;

            $progress = isset($c['progress_percent']) ? number_format($c['progress_percent'], 1) . '%' : '-';

            $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
            $xml .= $this->cellNum('A', $r, $idx + 1, 6);
            $xml .= $this->cellStr('B', $r, $c['customer_code'] ?? '-', 6);
            $xml .= $this->cellStr('C', $r, $c['customer_name'] ?? '-', 5);
            $xml .= $this->cellStr('D', $r, $c['agent_name'] ?? '-', 5);
            $xml .= $this->cellStr('E', $r, $c['package_name'] ?? '-', 5);
            $xml .= $this->cellStr('F', $r, $c['period_text'] ?? '-', 6);
            $xml .= $this->cellStr('G', $r, $c['cash_description'] ?? '-', 5);
            $xml .= $this->cellNum('H', $r, $cashAmount, 8);
            $xml .= $this->cellNum('I', $r, $totalPaid, 8);
            $xml .= $this->cellStr('J', $r, $progress, 6);
            $xml .= $this->cellStr('K', $r, $c['customer_phone'] ?? '-', 6);
            $xml .= $this->cellStr('L', $r, $c['customer_address'] ?? '-', 5);
            $xml .= "    </row>\n";
        }

        $lastDataRow = $r;

        // Total Row
        $r++;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, '', 9);
        $xml .= $this->cellStr('B', $r, 'TOTAL PENYIAPAN UANG TUNAI', 9);
        $xml .= $this->cellStr('C', $r, count($cashItems) . ' Konsumen Penerima', 9);
        $xml .= $this->cellStr('D', $r, '', 9);
        $xml .= $this->cellStr('E', $r, '', 9);
        $xml .= $this->cellStr('F', $r, '', 9);
        $xml .= $this->cellStr('G', $r, '', 9);
        $xml .= $this->cellNum('H', $r, $totCash, 9);
        $xml .= $this->cellNum('I', $r, $totPaid, 9);
        $xml .= $this->cellStr('J', $r, '', 9);
        $xml .= $this->cellStr('K', $r, '', 9);
        $xml .= $this->cellStr('L', $r, '', 9);
        $xml .= "    </row>\n";

        $xml .= '  </sheetData>' . "\n";
        if ($lastDataRow >= 6) {
            $xml .= "  <autoFilter ref=\"A5:L{$lastDataRow}\"/>\n";
        }
        $xml .= '  <printOptions gridLines="1"/>' . "\n";
        $xml .= '  <pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $xml .= '  <pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>' . "\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    /**
     * Sheet 9: Ringkasan Eksekutif Anggaran Pengadaan & Uang Tunai
     */
    private function buildSummarySheetXml(array $data, string $appName, string $period, string $date, string $scope): string
    {
        $sum = $data['summary'];
        $scopeLabel = $scope === 'all' || $scope === 'raw_all' ? 'Seluruh Pesanan Konsumen Aktif (PO ke Supplier)' : 'Hanya Konsumen Lolos Titik Aman / Ditetapkan Tutup Buku';

        $colWidths = [
            1 => 6,   // A: No
            2 => 36,  // B: Komponen Anggaran
            3 => 22,  // C: Volume / Kuantitas
            4 => 26,  // D: Estimasi Alokasi Dana (Rp)
            5 => 45,  // E: Catatan Operasional
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '  <sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>' . "\n";
        $xml .= '  <sheetFormatPr defaultRowHeight="20"/>' . "\n";
        $xml .= '  <cols>' . "\n";
        foreach ($colWidths as $col => $w) {
            $xml .= "    <col min=\"{$col}\" max=\"{$col}\" width=\"{$w}\" customWidth=\"1\"/>\n";
        }
        $xml .= '  </cols>' . "\n";
        $xml .= '  <sheetData>' . "\n";

        // Row 1: Title
        $r = 1;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, strtoupper($appName), 1);
        $xml .= "    </row>\n";

        // Row 2: Subtitle
        $r = 2;
        $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
        $xml .= $this->cellStr('A', $r, '9. RINGKASAN EKSEKUTIF KEBUTUHAN MODAL BELANJA & PENYIAPAN DANA OWNER', 2);
        $xml .= "    </row>\n";

        // Row 3: Meta Info
        $r = 3;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $xml .= $this->cellStr('A', $r, "Cakupan Data: {$scopeLabel} | Periode: {$period} | Dicetak: {$date}", 3);
        $xml .= "    </row>\n";

        // Row 4: Empty row
        $r = 4;
        $xml .= "    <row r=\"{$r}\" ht=\"10\"/>\n";

        // Row 5: Table Header
        $r = 5;
        $headers = [
            'A' => 'No',
            'B' => 'Komponen Anggaran',
            'C' => 'Volume / Kuantitas',
            'D' => 'Estimasi Kebutuhan Dana (Rp)',
            'E' => 'Keterangan & Rekomendasi Owner',
        ];
        $xml .= "    <row r=\"{$r}\" ht=\"28\">\n";
        foreach ($headers as $col => $text) {
            $xml .= $this->cellStr($col, $r, $text, 4);
        }
        $xml .= "    </row>\n";

        $summaryRows = [
            [
                'name' => '1. Belanja Barang Pokok (Parsel & Eceran)',
                'qty' => number_format($sum['total_base_pieces']) . ' Pcs (' . $sum['total_unique_items'] . ' Macam)',
                'amount' => (float)$sum['total_estimated_cost'],
                'note' => 'Pengadaan kulakan sembako, biskuit, wafer, minuman, mie instan ke supplier grosir.',
            ],
            [
                'name' => '2. Belanja Hadiah Bonus Paket',
                'qty' => number_format($sum['total_bonus_pieces']) . ' Pcs (' . $sum['total_unique_bonus'] . ' Macam)',
                'amount' => (float)$sum['total_bonus_cost'],
                'note' => 'Pengadaan hadiah bonus gratis tabungan & parsel untuk konsumen loyal.',
            ],
            [
                'name' => '3. Belanja Kemasan & Wadah Parsel',
                'qty' => number_format($sum['total_packaging_units']) . ' Unit (' . $sum['total_packaging_types'] . ' Model)',
                'amount' => (float)$sum['total_packaging_cost'],
                'note' => 'Pembelian koper 18 inch, container box, keranjang laundry, toples, dan lemari akrilik.',
            ],
            [
                'name' => '4. Penyiapan Uang Tunai Tabungan & Tutup Buku',
                'qty' => number_format($sum['total_cash_recipients']) . ' Konsumen (' . $sum['total_cash_agents'] . ' Agen)',
                'amount' => (float)$sum['total_cash_amount'],
                'note' => 'Dana uang tunai nasabah tabungan hari raya serta pencairan stop konsumen ter-ACC.',
            ],
        ];

        foreach ($summaryRows as $idx => $sr) {
            $r++;
            $xml .= "    <row r=\"{$r}\" ht=\"22\">\n";
            $xml .= $this->cellNum('A', $r, $idx + 1, 6);
            $xml .= $this->cellStr('B', $r, $sr['name'], 5);
            $xml .= $this->cellStr('C', $r, $sr['qty'], 6);
            $xml .= $this->cellNum('D', $r, $sr['amount'], 8);
            $xml .= $this->cellStr('E', $r, $sr['note'], 5);
            $xml .= "    </row>\n";
        }

        // Grand Total Row
        $r++;
        $xml .= "    <row r=\"{$r}\" ht=\"26\">\n";
        $xml .= $this->cellStr('A', $r, '', 9);
        $xml .= $this->cellStr('B', $r, 'TOTAL DANA MODAL KESELURUHAN (BELANJA + UANG TUNAI)', 9);
        $xml .= $this->cellStr('C', $r, number_format($sum['total_packages_count']) . ' Paket Terpenuhi', 9);
        $xml .= $this->cellNum('D', $r, (float)$sum['grand_total_fund'], 9);
        $xml .= $this->cellStr('E', $r, 'Total anggaran belanja kulakan grosir & uang tunai konsumen SPL', 9);
        $xml .= "    </row>\n";

        // Informational Rows
        $r += 2;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $xml .= $this->cellStr('A', $r, '* CATATAN OPERASIONAL PENGADAAN:', 3);
        $xml .= "    </row>\n";
        $r++;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $safeCount = $sum['safe_threshold_reached_count'] ?? 0;
        $pendingCount = $sum['pending_safe_count'] ?? 0;
        $xml .= $this->cellStr('A', $r, "- Total pesanan dianalisis: {$sum['total_eligible_customers']} paket ({$safeCount} lolos titik aman, {$pendingCount} dalam periode angsuran).", 3);
        $xml .= "    </row>\n";
        $r++;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $xml .= $this->cellStr('A', $r, "- Laporan ini disusun otomatis untuk memudahkan Purchase Order (PO) langsung kepada supplier pabrikan & agen grosir.", 3);
        $xml .= "    </row>\n";

        $xml .= '  </sheetData>' . "\n";
        $xml .= '  <printOptions gridLines="1"/>' . "\n";
        $xml .= '  <pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $xml .= '  <pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>' . "\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    private function cellStr(string $col, int $row, string $value, int $styleId = 0): string
    {
        $escaped = htmlspecialchars($value, ENT_XML1, 'UTF-8');
        return "      <c r=\"{$col}{$row}\" t=\"inlineStr\" s=\"{$styleId}\"><is><t>{$escaped}</t></is></c>\n";
    }

    private function cellNum(string $col, int $row, float|int $value, int $styleId = 0): string
    {
        return "      <c r=\"{$col}{$row}\" s=\"{$styleId}\"><v>{$value}</v></c>\n";
    }
}
