<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class MaintenanceService
{
    protected static ?bool $cachedActive = null;

    public static function filePath(): string
    {
        try {
            return storage_path('framework/maintenance_active.json');
        } catch (\Throwable $e) {
            return dirname(__DIR__, 2) . '/storage/framework/maintenance_active.json';
        }
    }

    /**
     * Periksa apakah sistem sedang dalam mode maintenance
     */
    public static function isActive(): bool
    {
        if (self::$cachedActive !== null) {
            return self::$cachedActive;
        }

        if (!file_exists(self::filePath())) {
            self::$cachedActive = false;
            return false;
        }

        $content = @file_get_contents(self::filePath());
        if (!$content) {
            self::$cachedActive = false;
            return false;
        }

        $data = json_decode($content, true);
        self::$cachedActive = !empty($data['active']);

        return self::$cachedActive;
    }

    /**
     * Ambil rincian data status maintenance
     */
    public static function getInfo(): array
    {
        $default = [
            'active' => false,
            'started_at' => null,
            'started_by_id' => null,
            'started_by_name' => null,
            'message' => 'Mohon maaf server kami sedang dalam perbaikan, mohon maaf untuk sementara segala bentuk aktivitas tidak dapat dilakukan.',
        ];

        if (!file_exists(self::filePath())) {
            return $default;
        }

        $content = @file_get_contents(self::filePath());
        if (!$content) {
            return $default;
        }

        $data = json_decode($content, true);
        return is_array($data) ? array_merge($default, $data) : $default;
    }

    /**
     * Aktifkan mode maintenance (Khusus Administrator)
     */
    public static function start(int $userId, string $userName, ?string $message = null): void
    {
        $data = [
            'active' => true,
            'started_at' => now()->toDateTimeString(),
            'started_by_id' => $userId,
            'started_by_name' => $userName,
            'message' => trim($message) ?: 'Mohon maaf server kami sedang dalam perbaikan, mohon maaf untuk sementara segala bentuk aktivitas tidak dapat dilakukan.',
        ];

        @file_put_contents(self::filePath(), json_encode($data, JSON_PRETTY_PRINT));
        self::$cachedActive = true;
        try {
            Cache::forever('spl_maintenance_active', true);
        } catch (\Throwable $e) {
            // Silently ignore cache store exception if database or cache is offline
        }
    }

    /**
     * Hentikan mode maintenance (Khusus Administrator)
     */
    public static function stop(): void
    {
        if (file_exists(self::filePath())) {
            @unlink(self::filePath());
        }

        self::$cachedActive = false;
        try {
            Cache::forget('spl_maintenance_active');
        } catch (\Throwable $e) {
            // Silently ignore cache store exception if database or cache is offline
        }
    }
}
