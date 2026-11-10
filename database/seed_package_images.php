<?php

$dir = __DIR__ . '/../public/uploads/packages';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$packages = [
    [
        'file' => 'sample_parsel_a.jpg',
        'title' => 'PAKET PARSEL MEWAH SULTAN (PAKET A)',
        'subtitle' => 'Cicilan: Rp 25.000 / minggu - Total: Rp 750.000',
        'desc' => 'Khong Guan, Monde, Marjan, Beras, Minyak, Gulaku, Kurma',
        'bg1' => [16, 78, 56],
        'bg2' => [6, 40, 28],
    ],
    [
        'file' => 'sample_parsel_b.jpg',
        'title' => 'PAKET PARSEL BERKAH KELUARGA (PAKET B)',
        'subtitle' => 'Cicilan: Rp 15.000 / minggu - Total: Rp 450.000',
        'desc' => 'Tango Wafer, ABC Sirup, Indomie, Minyak Goreng, Terigu',
        'bg1' => [13, 85, 105],
        'bg2' => [8, 48, 62],
    ],
    [
        'file' => 'sample_parsel_c.jpg',
        'title' => 'PAKET PARSEL MANIS LEBARAN (PAKET C)',
        'subtitle' => 'Cicilan: Rp 10.000 / minggu - Total: Rp 300.000',
        'desc' => 'Richeese Nabati, Marjan Sirup, Teh Celup, Astor Wafer',
        'bg1' => [130, 40, 60],
        'bg2' => [70, 20, 30],
    ],
    [
        'file' => 'sample_tabungan_1.jpg',
        'title' => 'PAKET TABUNGAN BERKAH 1 JUTA',
        'subtitle' => 'Cicilan: Rp 33.500 / minggu - CAIR RP 1.000.000 UTUH',
        'desc' => 'GRATIS BONUS: Khong Guan Classic Red 1600g',
        'bg1' => [180, 100, 20],
        'bg2' => [110, 50, 10],
    ],
];

foreach ($packages as $pkg) {
    $w = 800;
    $h = 500;
    $img = imagecreatetruecolor($w, $h);

    for ($y = 0; $y < $h; $y++) {
        $r = (int)($pkg['bg1'][0] + ($pkg['bg2'][0] - $pkg['bg1'][0]) * ($y / $h));
        $g = (int)($pkg['bg1'][1] + ($pkg['bg2'][1] - $pkg['bg1'][1]) * ($y / $h));
        $b = (int)($pkg['bg1'][2] + ($pkg['bg2'][2] - $pkg['bg1'][2]) * ($y / $h));
        $c = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, $w, $y, $c);
    }

    $gold = imagecolorallocate($img, 245, 197, 66);
    $white = imagecolorallocate($img, 255, 255, 255);
    $lightGold = imagecolorallocate($img, 255, 230, 150);

    // Decorative frame
    imagerectangle($img, 30, 30, $w - 30, $h - 30, $gold);
    imagerectangle($img, 34, 34, $w - 34, $h - 34, $gold);

    // Header badge
    imagestring($img, 5, 50, 55, 'SUSI PARSEL LEBARAN (SPL 2026)', $gold);
    imagestring($img, 5, 50, 100, $pkg['title'], $white);
    imagestring($img, 5, 50, 140, $pkg['subtitle'], $lightGold);
    imagestring($img, 4, 50, 185, 'Daftar Isi Paket:', $gold);
    imagestring($img, 4, 50, 215, $pkg['desc'], $white);
    imagestring($img, 4, 50, 420, 'Kemasan Hampers Box Elegan - Kualitas Terjamin', $lightGold);

    $outPath = $dir . '/' . $pkg['file'];
    imagejpeg($img, $outPath, 92);
    imagedestroy($img);
    echo "Saved: {$outPath}\n";
}

