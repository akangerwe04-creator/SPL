<?php

namespace App\Services;

use ZipArchive;
use Symfony\Component\HttpFoundation\Response;

class AgentReportExportService
{
    /**
     * Ekspor Laporan Rekapitulasi & Sub-data Agen ke format multi-sheet Excel (.xlsx) siap cetak
     */
    public function exportXlsx(array $reportData): Response
    {
        $agents = $reportData['agents'];
        $appSetting = $reportData['appSetting'];
        $appName = $appSetting->app_name ?: 'SPL (Susi Parsel Lebaran)';
        $programPeriod = $appSetting->program_period ?: 'Musim Lebaran 1447 H / 2026';
        $printedDate = date('d F Y H:i');

        $tmpFile = tempnam(sys_get_temp_dir(), 'spl_xlsx_');
        $zip = new ZipArchive();

        if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file temporary XLSX.');
        }

        // 1. Kumpulkan daftar sheets
        // Sheet 1: I. REKAPITULASI AGEN
        // Sheet 2..N+1: II. {Nama Agen} (1 sheet per agen)
        $sheets = [];
        $sheets[] = [
            'id' => 1,
            'name' => 'I. REKAPITULASI AGEN',
            'type' => 'summary',
            'agent' => null,
        ];

        $sheetIdx = 2;
        $usedSheetNames = ['I. REKAPITULASI AGEN' => true];

        foreach ($agents as $agent) {
            $baseName = 'II. ' . preg_replace('/[\\\\\\/\?\*\:\[\]]/', '', $agent->name);
            $cleanName = mb_substr($baseName, 0, 31);
            
            // Hindari duplikasi nama sheet di Excel
            $uniqueName = $cleanName;
            $counter = 1;
            while (isset($usedSheetNames[$uniqueName])) {
                $suffix = " ($counter)";
                $uniqueName = mb_substr($cleanName, 0, 31 - mb_strlen($suffix)) . $suffix;
                $counter++;
            }
            $usedSheetNames[$uniqueName] = true;

            $sheets[] = [
                'id' => $sheetIdx,
                'name' => $uniqueName,
                'type' => 'agent_detail',
                'agent' => $agent,
            ];
            $sheetIdx++;
        }

        // 2. Tambahkan [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', $this->buildContentTypesXml($sheets));

        // 3. Tambahkan _rels/.rels
        $zip->addFromString('_rels/.rels', $this->buildRootRelsXml());

        // 4. Tambahkan xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelsXml($sheets));

        // 5. Tambahkan xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbookXml($sheets));

        // 6. Tambahkan xl/styles.xml (Palet Warna SPL Pink & Siap Cetak)
        $zip->addFromString('xl/styles.xml', $this->buildStylesXml());

        // 7. Buat masing-masing Sheet XML
        foreach ($sheets as $s) {
            $path = "xl/worksheets/sheet{$s['id']}.xml";
            if ($s['type'] === 'summary') {
                $sheetXml = $this->buildSummarySheetXml($reportData, $appName, $programPeriod, $printedDate);
            } else {
                $sheetXml = $this->buildAgentDetailSheetXml($s['agent'], $appName, $programPeriod, $printedDate);
            }
            $zip->addFromString($path, $sheetXml);
        }

        $zip->close();

        $cleanAppName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $appName);
        $downloadName = "Laporan_Distribusi_Agen_{$cleanAppName}_" . date('Ymd_His') . ".xlsx";

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
'  <fonts count="6">' . "\n" .
'    <font><sz val="10"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="14"/><color rgb="FF831843"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="11"/><color rgb="FF0F172A"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><b/><sz val="10"/><color rgb="FF0F172A"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'    <font><sz val="9"/><color rgb="FF64748B"/><name val="Calibri"/><family val="2"/></font>' . "\n" .
'  </fonts>' . "\n" .
'  <fills count="5">' . "\n" .
'    <fill><patternFill patternType="none"/></fill>' . "\n" .
'    <fill><patternFill patternType="gray125"/></fill>' . "\n" .
'    <fill><patternFill patternType="solid"><fgColor rgb="FFBE185D"/><bgColor indexed="64"/></patternFill></fill>' . "\n" .
'    <fill><patternFill patternType="solid"><fgColor rgb="FFFCE7F1"/><bgColor indexed="64"/></patternFill></fill>' . "\n" .
'    <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/><bgColor indexed="64"/></patternFill></fill>' . "\n" .
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
'  <cellXfs count="10">' . "\n" .
'    <!-- 0: normal -->' . "\n" .
'    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' . "\n" .
'    <!-- 1: title -->' . "\n" .
'    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>' . "\n" .
'    <!-- 2: subtitle -->' . "\n" .
'    <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>' . "\n" .
'    <!-- 3: meta info -->' . "\n" .
'    <xf numFmtId="0" fontId="5" fillId="0" borderId="0" xfId="0" applyFont="1"/>' . "\n" .
'    <!-- 4: table header (bold white text, berry pink fill, border, center) -->' . "\n" .
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
'  </cellXfs>' . "\n" .
'  <cellStyles count="1">' . "\n" .
'    <cellStyle name="Normal" xfId="0" builtinId="0"/>' . "\n" .
'  </cellStyles>' . "\n" .
'</styleSheet>';
    }

    /**
     * Buat Sheet 1: I. REKAPITULASI AGEN, ALAMAT & JUMLAH PESANAN PER JENIS PAKET
     */
    private function buildSummarySheetXml(array $data, string $appName, string $period, string $date): string
    {
        $agents = $data['agents'];

        $colWidths = [
            1 => 6,   // A: No
            2 => 26,  // B: Nama Agen
            3 => 15,  // C: Username
            4 => 18,  // D: No HP / WA
            5 => 35,  // E: Alamat
            6 => 15,  // F: Parsel Harian
            7 => 17,  // G: Mingguan Lainnya
            8 => 16,  // H: Mingguan Eceran
            9 => 14,  // I: Tabungan
            10 => 14, // J: Total Paket
            11 => 16, // K: Total Konsumen
            12 => 22, // L: Total Target (Rp)
            13 => 22, // M: Total Setor (Rp)
            14 => 14, // N: Status Akun
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

        // Row 1: Title Usaha
        $r = 1;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, strtoupper($appName), 1);
        $xml .= "    </row>\n";

        // Row 2: Subtitle Laporan
        $r = 2;
        $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
        $xml .= $this->cellStr('A', $r, 'I. REKAPITULASI AGEN, ALAMAT & JUMLAH PESANAN PER JENIS PAKET', 2);
        $xml .= "    </row>\n";

        // Row 3: Meta Info
        $r = 3;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $xml .= $this->cellStr('A', $r, "Periode: {$period} | Tanggal Ekspor: {$date} | Total Agen: " . count($agents) . " Orang", 3);
        $xml .= "    </row>\n";

        // Row 4: Empty row
        $r = 4;
        $xml .= "    <row r=\"{$r}\" ht=\"10\"/>\n";

        // Row 5: Table Header
        $r = 5;
        $headers = [
            'A' => 'No',
            'B' => 'Nama Agen Lapangan',
            'C' => 'Username',
            'D' => 'No. HP / WhatsApp',
            'E' => 'Alamat Operasional Agen',
            'F' => 'Parsel Harian',
            'G' => 'Mingguan Lainnya',
            'H' => 'Mingguan Eceran',
            'I' => 'Tabungan',
            'J' => 'Total Paket',
            'K' => 'Total Konsumen',
            'L' => 'Total Target Nominal (Rp)',
            'M' => 'Total Setoran Terkumpul (Rp)',
            'N' => 'Status Akun',
        ];
        $xml .= "    <row r=\"{$r}\" ht=\"26\">\n";
        foreach ($headers as $col => $text) {
            $xml .= $this->cellStr($col, $r, $text, 4);
        }
        $xml .= "    </row>\n";

        // Rows Data
        $totParsel = 0;
        $totMingguan = 0;
        $totTabungan = 0;
        $totEcer = 0;
        $totPaket = 0;
        $totKonsumen = 0;
        $totTarget = 0;
        $totPaid = 0;

        foreach ($agents as $idx => $ag) {
            $r++;
            // Hitung breakdown paket
            $parselCount = (int) ($ag->total_harian_orders ?? 0);
            $mingguanCount = (int) ($ag->total_mingguan_orders ?? 0);
            $tabunganCount = (int) ($ag->total_tabungan_orders ?? 0);
            $ecerCount = (int) ($ag->total_ecer_orders ?? 0);

            // Fallback jika belum dihitung di controller
            if (!isset($ag->total_harian_orders)) {
                $parselCount = 0;
                $mingguanCount = 0;
                $tabunganCount = 0;
                $ecerCount = 0;
                foreach ($ag->customers as $cust) {
                    foreach ($cust->customerPackages as $cp) {
                        if ($cp->package) {
                            if ($cp->package->isEcer()) $ecerCount++;
                            elseif ($cp->package->isTabungan()) $tabunganCount++;
                            elseif ($cp->package->isMingguan()) $mingguanCount++;
                            else $parselCount++;
                        }
                    }
                }
            }

            $orderCount = (int) ($ag->total_orders ?? ($parselCount + $mingguanCount + $tabunganCount + $ecerCount));
            $custCount = (int) ($ag->total_customers ?? $ag->customers->count());
            $targetAmt = (float) ($ag->total_target_amount ?? 0);
            $paidAmt = (float) ($ag->total_paid_amount ?? 0);

            $totParsel += $parselCount;
            $totMingguan += $mingguanCount;
            $totTabungan += $tabunganCount;
            $totEcer += $ecerCount;
            $totPaket += $orderCount;
            $totKonsumen += $custCount;
            $totTarget += $targetAmt;
            $totPaid += $paidAmt;

            $statusText = $ag->is_active ? 'Aktif' : 'Nonaktif';

            $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
            $xml .= $this->cellNum('A', $r, $idx + 1, 6);
            $xml .= $this->cellStr('B', $r, $ag->name, 5);
            $xml .= $this->cellStr('C', $r, $ag->username ?: '-', 6);
            $xml .= $this->cellStr('D', $r, $ag->phone ?: '-', 6);
            $xml .= $this->cellStr('E', $r, $ag->address ?: '-', 5);
            $xml .= $this->cellNum('F', $r, $parselCount, 6);
            $xml .= $this->cellNum('G', $r, $mingguanCount, 6);
            $xml .= $this->cellNum('H', $r, $ecerCount, 6);
            $xml .= $this->cellNum('I', $r, $tabunganCount, 6);
            $xml .= $this->cellNum('J', $r, $orderCount, 6);
            $xml .= $this->cellNum('K', $r, $custCount, 6);
            $xml .= $this->cellNum('L', $r, $targetAmt, 8);
            $xml .= $this->cellNum('M', $r, $paidAmt, 8);
            $xml .= $this->cellStr('N', $r, $statusText, 6);
            $xml .= "    </row>\n";
        }

        // Summary Total Row
        $r++;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, '', 9);
        $xml .= $this->cellStr('B', $r, 'TOTAL KESELURUHAN', 9);
        $xml .= $this->cellStr('C', $r, '', 9);
        $xml .= $this->cellStr('D', $r, '', 9);
        $xml .= $this->cellStr('E', $r, '', 9);
        $xml .= $this->cellNum('F', $r, $totParsel, 9);
        $xml .= $this->cellNum('G', $r, $totMingguan, 9);
        $xml .= $this->cellNum('H', $r, $totEcer, 9);
        $xml .= $this->cellNum('I', $r, $totTabungan, 9);
        $xml .= $this->cellNum('J', $r, $totPaket, 9);
        $xml .= $this->cellNum('K', $r, $totKonsumen, 9);
        $xml .= $this->cellNum('L', $r, $totTarget, 9);
        $xml .= $this->cellNum('M', $r, $totPaid, 9);
        $xml .= $this->cellStr('N', $r, '', 9);
        $xml .= "    </row>\n";

        // --- SECTION B: REKAPITULASI PAKET PARSEL HARIAN PER AGEN ---
        $harianPackages = $data['harianPackages'] ?? [];
        if (!empty($harianPackages) && count($harianPackages) > 0) {
            $r += 2;
            $xml .= "    <row r=\"{$r}\" ht=\"22\">\n";
            $xml .= $this->cellStr('A', $r, 'REKAPITULASI I: PAKET PARSEL HARIAN PER AGEN', 2);
            $xml .= "    </row>\n";

            $r++;
            $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
            $xml .= $this->cellStr('A', $r, 'No', 4);
            $xml .= $this->cellStr('B', $r, 'Nama Agen Lapangan', 4);
            $xml .= $this->cellStr('C', $r, 'Alamat Agen', 4);
            $xml .= $this->cellStr('D', $r, 'Jml Konsumen (Harian)', 4);
            $colIdx = 5;
            foreach ($harianPackages as $pkg) {
                $colLet = $this->colLetter($colIdx++);
                $xml .= $this->cellStr($colLet, $r, $pkg->code . ' (' . $pkg->installment_count . ' Hari)', 4);
            }
            $totHarianCol = $this->colLetter($colIdx);
            $xml .= $this->cellStr($totHarianCol, $r, 'Total Parsel Harian', 4);
            $xml .= "    </row>\n";

            foreach ($agents as $idx => $ag) {
                $r++;
                $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
                $xml .= $this->cellNum('A', $r, $idx + 1, 6);
                $xml .= $this->cellStr('B', $r, $ag->name, 5);
                $xml .= $this->cellStr('C', $r, $ag->address ?: '-', 5);
                $xml .= $this->cellNum('D', $r, (int)($ag->harian_customers_count ?? 0), 6);
                $cIdx = 5;
                foreach ($harianPackages as $pkg) {
                    $cLet = $this->colLetter($cIdx++);
                    $cnt = (int)($ag->harian_package_counts[$pkg->id] ?? 0);
                    $xml .= $this->cellNum($cLet, $r, $cnt, 6);
                }
                $xml .= $this->cellNum($totHarianCol, $r, (int)($ag->total_harian_orders ?? 0), 6);
                $xml .= "    </row>\n";
            }

            // Total Parsel Harian Row
            $r++;
            $xml .= "    <row r=\"{$r}\" ht=\"22\">\n";
            $xml .= $this->cellStr('A', $r, '', 9);
            $xml .= $this->cellStr('B', $r, 'TOTAL PARSEL HARIAN SE-SPL', 9);
            $xml .= $this->cellStr('C', $r, '', 9);
            $xml .= $this->cellNum('D', $r, (int)($data['totalHarianCustomersCount'] ?? 0), 9);
            $cIdx = 5;
            foreach ($harianPackages as $pkg) {
                $cLet = $this->colLetter($cIdx++);
                $tot = (int)($data['harianPackageTotals'][$pkg->id] ?? 0);
                $xml .= $this->cellNum($cLet, $r, $tot, 9);
            }
            $xml .= $this->cellNum($totHarianCol, $r, (int)($data['totalHarianOrdersCount'] ?? 0), 9);
            $xml .= "    </row>\n";
        }

        // --- SECTION C: REKAPITULASI PAKET MINGGUAN LAINNYA / STANDAR PER AGEN ---
        $mingguanPackages = $data['mingguanPackages'] ?? [];
        if (!empty($mingguanPackages) && count($mingguanPackages) > 0) {
            $r += 2;
            $xml .= "    <row r=\"{$r}\" ht=\"22\">\n";
            $xml .= $this->cellStr('A', $r, 'REKAPITULASI II: PAKET MINGGUAN LAINNYA / STANDAR PER AGEN', 2);
            $xml .= "    </row>\n";

            $r++;
            $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
            $xml .= $this->cellStr('A', $r, 'No', 4);
            $xml .= $this->cellStr('B', $r, 'Nama Agen Lapangan', 4);
            $xml .= $this->cellStr('C', $r, 'Alamat Agen', 4);
            $xml .= $this->cellStr('D', $r, 'Jml Konsumen (Mingguan)', 4);
            $colIdx = 5;
            foreach ($mingguanPackages as $pkg) {
                $colLet = $this->colLetter($colIdx++);
                $xml .= $this->cellStr($colLet, $r, $pkg->code . ' (' . $pkg->installment_count . ' Minggu)', 4);
            }
            $totMingguanCol = $this->colLetter($colIdx);
            $xml .= $this->cellStr($totMingguanCol, $r, 'Total Paket Mingguan', 4);
            $xml .= "    </row>\n";

            foreach ($agents as $idx => $ag) {
                $r++;
                $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
                $xml .= $this->cellNum('A', $r, $idx + 1, 6);
                $xml .= $this->cellStr('B', $r, $ag->name, 5);
                $xml .= $this->cellStr('C', $r, $ag->address ?: '-', 5);
                $xml .= $this->cellNum('D', $r, (int)($ag->mingguan_customers_count ?? 0), 6);
                $cIdx = 5;
                foreach ($mingguanPackages as $pkg) {
                    $cLet = $this->colLetter($cIdx++);
                    $cnt = (int)($ag->mingguan_package_counts[$pkg->id] ?? 0);
                    $xml .= $this->cellNum($cLet, $r, $cnt, 6);
                }
                $xml .= $this->cellNum($totMingguanCol, $r, (int)($ag->total_mingguan_orders ?? 0), 6);
                $xml .= "    </row>\n";
            }

            // Total Paket Mingguan Row
            $r++;
            $xml .= "    <row r=\"{$r}\" ht=\"22\">\n";
            $xml .= $this->cellStr('A', $r, '', 9);
            $xml .= $this->cellStr('B', $r, 'TOTAL PAKET MINGGUAN LAINNYA SE-SPL', 9);
            $xml .= $this->cellStr('C', $r, '', 9);
            $xml .= $this->cellNum('D', $r, (int)($data['totalMingguanCustomersCount'] ?? 0), 9);
            $cIdx = 5;
            foreach ($mingguanPackages as $pkg) {
                $cLet = $this->colLetter($cIdx++);
                $tot = (int)($data['mingguanPackageTotals'][$pkg->id] ?? 0);
                $xml .= $this->cellNum($cLet, $r, $tot, 9);
            }
            $xml .= $this->cellNum($totMingguanCol, $r, (int)($data['totalMingguanOrdersCount'] ?? 0), 9);
            $xml .= "    </row>\n";
        }

        // --- SECTION D: REKAPITULASI PARSEL ECERAN MINGGUAN (PER BARANG ECERAN) ---
        $ecerItemColumns = $data['ecerItemColumns'] ?? [];
        if (!empty($ecerItemColumns) && count($ecerItemColumns) > 0) {
            $r += 2;
            $xml .= "    <row r=\"{$r}\" ht=\"22\">\n";
            $xml .= $this->cellStr('A', $r, 'REKAPITULASI III: PARSEL ECERAN (MINGGUAN) & PERSEBARAN BARANG ECERAN', 2);
            $xml .= "    </row>\n";

            $r++;
            $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
            $xml .= $this->cellStr('A', $r, 'Format: No, Nama Agen, Alamat, Jumlah Konsumen (Eceran), dan Nama-nama Barang Eceran (Jumlah Konsumen per Barang)', 3);
            $xml .= "    </row>\n";

            $r++;
            $xml .= "    <row r=\"{$r}\" ht=\"26\">\n";
            $xml .= $this->cellStr('A', $r, 'No', 4);
            $xml .= $this->cellStr('B', $r, 'Nama Agen Lapangan', 4);
            $xml .= $this->cellStr('C', $r, 'Alamat Agen', 4);
            $xml .= $this->cellStr('D', $r, 'Jml Konsumen (Eceran)', 4);
            $cIdx = 5;
            foreach ($ecerItemColumns as $colName) {
                $cLet = $this->colLetter($cIdx++);
                $xml .= $this->cellStr($cLet, $r, $colName, 4);
            }
            $totEcerCol = $this->colLetter($cIdx);
            $xml .= $this->cellStr($totEcerCol, $r, 'Total Item Eceran', 4);
            $xml .= "    </row>\n";

            foreach ($agents as $idx => $ag) {
                $r++;
                $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
                $xml .= $this->cellNum('A', $r, $idx + 1, 6);
                $xml .= $this->cellStr('B', $r, $ag->name, 5);
                $xml .= $this->cellStr('C', $r, $ag->address ?: '-', 5);
                $xml .= $this->cellNum('D', $r, (int)($ag->ecer_customers_count ?? 0), 6);
                $cIdx = 5;
                foreach ($ecerItemColumns as $colName) {
                    $cLet = $this->colLetter($cIdx++);
                    $cnt = (int)($ag->ecer_item_counts[$colName] ?? 0);
                    $xml .= $this->cellNum($cLet, $r, $cnt, 6);
                }
                $xml .= $this->cellNum($totEcerCol, $r, (int)($ag->total_ecer_orders ?? 0), 6);
                $xml .= "    </row>\n";
            }

            // Total Parsel Eceran Row
            $r++;
            $xml .= "    <row r=\"{$r}\" ht=\"22\">\n";
            $xml .= $this->cellStr('A', $r, '', 9);
            $xml .= $this->cellStr('B', $r, 'TOTAL PARSEL ECERAN SE-SPL', 9);
            $xml .= $this->cellStr('C', $r, '', 9);
            $xml .= $this->cellNum('D', $r, (int)($data['totalEcerCustomersCount'] ?? 0), 9);
            $cIdx = 5;
            foreach ($ecerItemColumns as $colName) {
                $cLet = $this->colLetter($cIdx++);
                $tot = (int)($data['ecerItemTotals'][$colName]['customers'] ?? 0);
                $xml .= $this->cellNum($cLet, $r, $tot, 9);
            }
            $xml .= $this->cellNum($totEcerCol, $r, (int)($data['totalEcerOrdersCount'] ?? 0), 9);
            $xml .= "    </row>\n";
        }

        $xml .= '  </sheetData>' . "\n";

        // Print & Page Setup (Landscape, Fit to Page)
        $xml .= '  <printOptions gridLines="1"/>' . "\n";
        $xml .= '  <pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $xml .= '  <pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>' . "\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    /**
     * Buat Sheet Detail Konsumen & Paket per Agen
     */
    private function buildAgentDetailSheetXml($agent, string $appName, string $period, string $date): string
    {
        $colWidths = [
            1 => 6,   // A: No
            2 => 16,  // B: Kode Konsumen
            3 => 26,  // C: Nama Konsumen
            4 => 16,  // D: No HP / WA
            5 => 32,  // E: Alamat Konsumen
            6 => 32,  // F: Paket Dipesan
            7 => 15,  // G: Jenis Paket
            8 => 18,  // H: Skema & Durasi
            9 => 20,  // I: Target Nominal (Rp)
            10 => 20, // J: Total Disetor (Rp)
            11 => 20, // K: Sisa Tagihan (Rp)
            12 => 14, // L: Progress Setor
            13 => 16, // M: Status
            14 => 28, // N: Catatan Khusus
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
        $xml .= $this->cellStr('A', $r, 'II. RINCIAN SUB-DATA KONSUMEN & PAKET YANG DIPILIH PER AGEN', 2);
        $xml .= "    </row>\n";

        // Row 3: Info Agen
        $r = 3;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $agentInfo = "Agen Penanggung Jawab: {$agent->name} | Telp: " . ($agent->phone ?: '-') . " | Alamat: " . ($agent->address ?: '-');
        $xml .= $this->cellStr('A', $r, $agentInfo, 2);
        $xml .= "    </row>\n";

        // Row 4: Meta info
        $r = 4;
        $xml .= "    <row r=\"{$r}\" ht=\"16\">\n";
        $xml .= $this->cellStr('A', $r, "Periode: {$period} | Dicetak: {$date} | Total Konsumen Binaan: " . $agent->customers->count() . " Orang", 3);
        $xml .= "    </row>\n";

        // Row 5: Empty row
        $r = 5;
        $xml .= "    <row r=\"{$r}\" ht=\"10\"/>\n";

        // Row 6: Table Header
        $r = 6;
        $headers = [
            'A' => 'No',
            'B' => 'Kode Konsumen',
            'C' => 'Nama Konsumen',
            'D' => 'No. HP / WA',
            'E' => 'Alamat Konsumen',
            'F' => 'Paket Dipesan',
            'G' => 'Jenis Paket',
            'H' => 'Skema & Durasi',
            'I' => 'Target Nominal (Rp)',
            'J' => 'Total Disetor (Rp)',
            'K' => 'Sisa Tagihan (Rp)',
            'L' => 'Progress',
            'M' => 'Status',
            'N' => 'Catatan Khusus',
        ];
        $xml .= "    <row r=\"{$r}\" ht=\"26\">\n";
        foreach ($headers as $col => $text) {
            $xml .= $this->cellStr($col, $r, $text, 4);
        }
        $xml .= "    </row>\n";

        // Data Rows
        $rowNo = 1;
        $totTarget = 0;
        $totPaid = 0;
        $totRemaining = 0;

        foreach ($agent->customers as $cust) {
            $packagesList = $cust->customerPackages;
            if ($packagesList->isEmpty()) {
                $r++;
                $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
                $xml .= $this->cellNum('A', $r, $rowNo++, 6);
                $xml .= $this->cellStr('B', $r, $cust->code ?: '-', 6);
                $xml .= $this->cellStr('C', $r, $cust->name, 5);
                $xml .= $this->cellStr('D', $r, $cust->phone ?: '-', 6);
                $xml .= $this->cellStr('E', $r, $cust->address ?: '-', 5);
                $xml .= $this->cellStr('F', $r, 'Belum memilih paket', 5);
                $xml .= $this->cellStr('G', $r, '-', 6);
                $xml .= $this->cellStr('H', $r, '-', 6);
                $xml .= $this->cellNum('I', $r, 0, 8);
                $xml .= $this->cellNum('J', $r, 0, 8);
                $xml .= $this->cellNum('K', $r, 0, 8);
                $xml .= $this->cellStr('L', $r, '0%', 6);
                $xml .= $this->cellStr('M', $r, 'Belum Aktif', 6);
                $xml .= $this->cellStr('N', $r, '-', 5);
                $xml .= "    </row>\n";
            } else {
                foreach ($packagesList as $cp) {
                    $r++;
                    $pkg = $cp->package;
                    $pkgName = $pkg ? $pkg->name : 'Paket Kustom';
                    $pkgType = $pkg ? ucfirst($pkg->type) : 'Parsel';
                    $periodText = $pkg ? (($pkg->installment_label ?? 'Mingguan') . ' (' . $pkg->installment_count . ' ' . ($pkg->period_label ?? 'Mgg') . ')') : '-';
                    $target = (float) $cp->total_target;
                    $paid = (float) $cp->total_paid;
                    $remaining = max(0, $target - $paid);
                    $progress = $cp->progress_percent . '%';
                    $status = $cp->status === 'completed' ? 'Lunas Sah' : ($cp->status === 'canceled' ? 'Dibatalkan' : 'Berjalan');

                    $notes = [];
                    if ($cp->package && $cp->package->isEcer()) {
                        $itemDetails = [];
                        if ($cp->customItems && $cp->customItems->isNotEmpty()) {
                            foreach ($cp->customItems as $cItem) {
                                $itemDetails[] = $cItem->item_name . ($cItem->quantity > 1 ? ' (' . $cItem->quantity . ')' : '');
                            }
                        } elseif ($cp->package->packageItems && $cp->package->packageItems->isNotEmpty()) {
                            foreach ($cp->package->packageItems as $pItem) {
                                $name = $pItem->item ? $pItem->item->name : 'Item';
                                $itemDetails[] = $name . ($pItem->quantity > 1 ? ' (' . $pItem->quantity . ')' : '');
                            }
                        }
                        if (!empty($itemDetails)) {
                            $notes[] = 'Barang: ' . implode(', ', $itemDetails);
                        }
                    }
                    if ($cp->is_bonus_forfeited) {
                        $notes[] = 'Bonus Hangus';
                    }
                    if ($cp->fulfillment_status && $cp->fulfillment_status !== 'pending') {
                        $notes[] = 'Status: ' . ucfirst($cp->fulfillment_status);
                    }
                    if ($cust->notes) {
                        $notes[] = $cust->notes;
                    }
                    $noteStr = !empty($notes) ? implode(' | ', $notes) : '-';

                    $totTarget += $target;
                    $totPaid += $paid;
                    $totRemaining += $remaining;

                    $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
                    $xml .= $this->cellNum('A', $r, $rowNo++, 6);
                    $xml .= $this->cellStr('B', $r, $cust->code ?: '-', 6);
                    $xml .= $this->cellStr('C', $r, $cust->name, 5);
                    $xml .= $this->cellStr('D', $r, $cust->phone ?: '-', 6);
                    $xml .= $this->cellStr('E', $r, $cust->address ?: '-', 5);
                    $xml .= $this->cellStr('F', $r, $pkgName, 5);
                    $xml .= $this->cellStr('G', $r, $pkgType, 6);
                    $xml .= $this->cellStr('H', $r, $periodText, 6);
                    $xml .= $this->cellNum('I', $r, $target, 8);
                    $xml .= $this->cellNum('J', $r, $paid, 8);
                    $xml .= $this->cellNum('K', $r, $remaining, 8);
                    $xml .= $this->cellStr('L', $r, $progress, 6);
                    $xml .= $this->cellStr('M', $r, $status, 6);
                    $xml .= $this->cellStr('N', $r, $noteStr, 5);
                    $xml .= "    </row>\n";
                }
            }
        }

        // Summary Total Row for this Agent
        $r++;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, '', 9);
        $xml .= $this->cellStr('B', $r, 'TOTAL BINAAN AGEN ' . strtoupper($agent->name), 9);
        $xml .= $this->cellStr('C', $r, '', 9);
        $xml .= $this->cellStr('D', $r, '', 9);
        $xml .= $this->cellStr('E', $r, '', 9);
        $xml .= $this->cellStr('F', $r, '', 9);
        $xml .= $this->cellStr('G', $r, '', 9);
        $xml .= $this->cellStr('H', $r, ($rowNo - 1) . ' Pesanan', 9);
        $xml .= $this->cellNum('I', $r, $totTarget, 9);
        $xml .= $this->cellNum('J', $r, $totPaid, 9);
        $xml .= $this->cellNum('K', $r, $totRemaining, 9);
        $xml .= $this->cellStr('L', $r, '', 9);
        $xml .= $this->cellStr('M', $r, '', 9);
        $xml .= $this->cellStr('N', $r, '', 9);
        $xml .= "    </row>\n";

        $xml .= '  </sheetData>' . "\n";

        // Print & Page Setup
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

    private function colLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $mod = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colIndex = intdiv($colIndex - $mod, 26);
        }
        return $letter;
    }

    /**
     * Ekspor Data Akun, Username, Kata Sandi (Password) & Kontak Agen Lapangan ke format Excel (.xlsx)
     */
    public function exportAgentAccountsXlsx($agents, $appSetting): Response
    {
        $appName = $appSetting->app_name ?: 'SPL (Susi Parsel Lebaran)';
        $programPeriod = $appSetting->program_period ?: 'Musim Lebaran 1447 H / 2026';
        $printedDate = date('d F Y H:i');

        $tmpFile = tempnam(sys_get_temp_dir(), 'spl_agents_');
        $zip = new ZipArchive();

        if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file temporary XLSX.');
        }

        $sheets = [
            [
                'id' => 1,
                'name' => 'DATA AKUN AGEN',
                'type' => 'agent_accounts',
            ]
        ];

        $zip->addFromString('[Content_Types].xml', $this->buildContentTypesXml($sheets));
        $zip->addFromString('_rels/.rels', $this->buildRootRelsXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelsXml($sheets));
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbookXml($sheets));
        $zip->addFromString('xl/styles.xml', $this->buildStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildAgentAccountsSheetXml($agents, $appName, $programPeriod, $printedDate));

        $zip->close();

        $cleanAppName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $appName);
        $downloadName = "Data_Akun_dan_Password_Agen_{$cleanAppName}_" . date('Ymd_His') . ".xlsx";

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

    /**
     * Buat Sheet Data Akun Agen (Nama, Username, Password, No HP, Alamat, Status)
     */
    private function buildAgentAccountsSheetXml($agents, string $appName, string $period, string $date): string
    {
        $colWidths = [
            1 => 6,   // A: No
            2 => 28,  // B: Nama Lengkap Agen
            3 => 18,  // C: Username Login
            4 => 20,  // D: Kata Sandi / Password
            5 => 20,  // E: Nomor WhatsApp / HP
            6 => 36,  // F: Alamat Lengkap
            7 => 14,  // G: Status Akun
            8 => 18,  // H: Konsumen Binaan
            9 => 22,  // I: Total Kas Setor (Rp)
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

        // Row 1: Title Usaha
        $r = 1;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, strtoupper($appName), 1);
        $xml .= "    </row>\n";

        // Row 2: Subtitle Laporan
        $r = 2;
        $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
        $xml .= $this->cellStr('A', $r, 'MASTER DATA AKUN, USERNAME, KATA SANDI (PASSWORD) & KONTAK AGEN LAPANGAN', 2);
        $xml .= "    </row>\n";

        // Row 3: Meta Info
        $r = 3;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $xml .= $this->cellStr('A', $r, "Periode: {$period} | Tanggal Ekspor: {$date} | Total Agen: " . count($agents) . " Orang | Dokumen Rahasia Pengelola", 3);
        $xml .= "    </row>\n";

        // Row 4: Empty row
        $r = 4;
        $xml .= "    <row r=\"{$r}\" ht=\"10\"/>\n";

        // Row 5: Table Header
        $r = 5;
        $headers = [
            'A' => 'No',
            'B' => 'Nama Lengkap Agen',
            'C' => 'Username Login',
            'D' => 'Kata Sandi / Password',
            'E' => 'Nomor WhatsApp / HP',
            'F' => 'Alamat Domisili / Operasional',
            'G' => 'Status Akun',
            'H' => 'Konsumen Binaan',
            'I' => 'Total Kas Setor (Rp)',
        ];

        $xml .= "    <row r=\"{$r}\" ht=\"26\">\n";
        foreach ($headers as $col => $text) {
            $xml .= $this->cellStr($col, $r, $text, 4);
        }
        $xml .= "    </row>\n";

        // Data Rows
        $no = 1;
        $totCustomers = 0;
        $totCollected = 0;

        foreach ($agents as $agent) {
            $r++;
            $customersCount = (int)($agent->customers_count ?? 0);
            $collected = (float)($agent->total_collected ?? 0);
            $statusStr = $agent->is_active ? 'Aktif' : 'Nonaktif';
            $passwordStr = $agent->plain_password ?: 'password';

            $totCustomers += $customersCount;
            $totCollected += $collected;

            $xml .= "    <row r=\"{$r}\" ht=\"20\">\n";
            $xml .= $this->cellNum('A', $r, $no++, 6);
            $xml .= $this->cellStr('B', $r, $agent->name, 5);
            $xml .= $this->cellStr('C', $r, $agent->username, 6);
            $xml .= $this->cellStr('D', $r, $passwordStr, 6);
            $xml .= $this->cellStr('E', $r, $agent->phone ?: '-', 6);
            $xml .= $this->cellStr('F', $r, $agent->address ?: '-', 5);
            $xml .= $this->cellStr('G', $r, $statusStr, 6);
            $xml .= $this->cellStr('H', $r, $customersCount . ' Orang', 6);
            $xml .= $this->cellNum('I', $r, $collected, 8);
            $xml .= "    </row>\n";
        }

        // Summary Total Row
        $r++;
        $xml .= "    <row r=\"{$r}\" ht=\"24\">\n";
        $xml .= $this->cellStr('A', $r, '', 9);
        $xml .= $this->cellStr('B', $r, 'TOTAL KESELURUHAN AGEN (' . count($agents) . ' AKUN)', 9);
        $xml .= $this->cellStr('C', $r, '', 9);
        $xml .= $this->cellStr('D', $r, '', 9);
        $xml .= $this->cellStr('E', $r, '', 9);
        $xml .= $this->cellStr('F', $r, '', 9);
        $xml .= $this->cellStr('G', $r, '', 9);
        $xml .= $this->cellStr('H', $r, $totCustomers . ' Orang', 9);
        $xml .= $this->cellNum('I', $r, $totCollected, 9);
        $xml .= "    </row>\n";

        // Row note
        $r += 2;
        $xml .= "    <row r=\"{$r}\" ht=\"18\">\n";
        $xml .= $this->cellStr('A', $r, '* CATATAN KEAMANAN: Data username dan password di atas adalah kredensial login resmi. Simpan file ini secara rahasia untuk keperluan koordinasi pemilik usaha dan agen.', 3);
        $xml .= "    </row>\n";

        $xml .= '  </sheetData>' . "\n";
        $xml .= '  <printOptions gridLines="1"/>' . "\n";
        $xml .= '  <pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $xml .= '  <pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>' . "\n";
        $xml .= '</worksheet>';

        return $xml;
    }
}

