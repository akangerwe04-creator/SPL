<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ImageOptimizer
{
    /**
     * Kompresi dan optimasi foto unggahan agar file HP besar (3-5MB)
     * menjadi foto tajam dan ringan (~100-180 KB)
     */
    public static function optimizeAndSave(UploadedFile $file, string $subfolder = 'posts', int $maxWidth = 1200, int $quality = 78): array
    {
        $destinationDir = public_path('uploads/' . trim($subfolder, '/'));
        if (!file_exists($destinationDir)) {
            @mkdir($destinationDir, 0777, true);
        }

        $filename = 'spl_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
        $fullPath = $destinationDir . DIRECTORY_SEPARATOR . $filename;
        $relativePath = 'uploads/' . trim($subfolder, '/') . '/' . $filename;

        $sourcePath = $file->getRealPath();
        $mime = $file->getMimeType();

        // Buat GD image berdasarkan mime type
        $srcImage = null;
        if (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
            $srcImage = @imagecreatefromjpeg($sourcePath);
        } elseif (str_contains($mime, 'png')) {
            $srcImage = @imagecreatefrompng($sourcePath);
        } elseif (str_contains($mime, 'webp')) {
            $srcImage = @imagecreatefromwebp($sourcePath);
        }

        if (!$srcImage) {
            // Fallback jika bukan gambar yang didukung GD atau GD error
            $file->move($destinationDir, $filename);
            $sizeKb = (int) round(@filesize($fullPath) / 1024);
            return [
                'path' => $relativePath,
                'size_kb' => $sizeKb,
            ];
        }

        // Ambil orientasi EXIF jika ada (untuk foto kamera HP agar tidak miring)
        if (function_exists('exif_read_data') && (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg'))) {
            $exif = @exif_read_data($sourcePath);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $srcImage = imagerotate($srcImage, 180, 0);
                        break;
                    case 6:
                        $srcImage = imagerotate($srcImage, -90, 0);
                        break;
                    case 8:
                        $srcImage = imagerotate($srcImage, 90, 0);
                        break;
                }
            }
        }

        $origWidth = imagesx($srcImage);
        $origHeight = imagesy($srcImage);

        // Hitung proporsi resize
        if ($origWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) round(($origHeight / $origWidth) * $maxWidth);
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }

        // Buat canvas gambar baru resolusi optimal
        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        // Isi latar putih (jika ada transparansi png)
        $white = imagecolorallocate($dstImage, 255, 255, 255);
        imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $white);

        // Resampling berkualitas tinggi
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Simpan sebagai JPEG berkualitas tinggi terkompresi
        imagejpeg($dstImage, $fullPath, $quality);

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        $sizeKb = (int) round(@filesize($fullPath) / 1024);

        return [
            'path' => $relativePath,
            'size_kb' => $sizeKb,
        ];
    }
}

