<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\CatalogSetting;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Menyiapkan Akun Owner utama, Akun Admin tersembunyi, dan Pengaturan Aplikasi awal.
     * Data master dan operasional dibiarkan bersih (tanpa data demo).
     */
    public function run(): void
    {
        // 1. BUAT / PASTIKAN AKUN OWNER UTAMA SECARA PERMANEN (USERNAME: owner, PASSWORD: ownerspl)
        $owner = User::ensurePermanentOwner();

        // 2. BUAT / PASTIKAN AKUN ADMIN TERSEMBUNYI (OTORITAS TERTINGGI)
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@spl.com',
                'phone' => '0811-0000-9999',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
                'is_active' => true,
            ]
        );

        if ($admin->role !== 'admin') {
            $admin->update([
                'role' => 'admin',
            ]);
        }

        // 3. BUAT / PASTIKAN PENGATURAN APLIKASI
        CatalogSetting::ensurePermanentLogo();
        $setting = CatalogSetting::first();
        $permLogo = file_exists(public_path('permanent/logo.png')) ? 'permanent/logo.png' : null;

        if (!$setting) {
            $setting = CatalogSetting::create([
                'app_name' => 'SPL (Susi Parsel Lebaran)',
                'logo_path' => $permLogo,
                'owner_name' => 'Susi',
                'owner_address' => 'Jl. Pasar Tradisional No. 88, Blok Melati, Jawa Barat',
                'title' => 'KATALOG SPL (SUSI PARSEL LEBARAN)',
                'program_period' => 'Periode Musim Lebaran 1447 H / 2026',
                'program_start_date' => '2025-08-17',
                'program_end_date' => '2026-03-15',
                'terms_and_conditions' => "1. Pembayaran iuran setoran wajib dilakukan setiap minggu sesuai nominal yang disepakati.\n2. Untuk paket tabungan, bonus barang hangus apabila konsumen tidak setor selama 3 minggu berturut-turut.\n3. Apabila konsumen membatalkan pesanan / mundur di tengah jalan, dikenakan biaya administrasi 10% dari total uang yang telah disetor.\n4. Penyerahan parsel dan tabungan dilakukan H-7 sebelum Hari Raya Idul Fitri 1447 H.\n5. Konsumen yang belum lunas dapat memilih penyesuaian barang (Mini-POS) sesuai dengan saldo setoran yang telah masuk.",
                'contact_person' => '0812-3456-7890 (Susi - Owner SPL)',
                'bank_account_info' => 'Bank BCA: 123-456-789 a.n. Susi',
            ]);
        } else {
            $setting->update([
                'app_name' => 'SPL (Susi Parsel Lebaran)',
                'owner_name' => 'Susi',
                'logo_path' => ($setting->logo_path && file_exists(public_path($setting->logo_path))) ? $setting->logo_path : $permLogo,
            ]);
        }

        if ($setting) {
            \App\Http\Controllers\AppSettingController::syncPwaAssetsAndManifest($setting);
        }
    }
}
