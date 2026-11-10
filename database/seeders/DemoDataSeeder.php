<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Item;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\CustomerCustomItem;
use App\Models\AgentDeposit;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Comment;
use App\Models\CatalogSetting;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed sample/demo data for development or testing if explicitly requested.
     */
    public function run(): void
    {
        // 1. BUAT / PASTIKAN AKUN OWNER UTAMA SECARA PERMANEN (USERNAME: owner, PASSWORD: ownerspl)
        $owner = User::ensurePermanentOwner();

        $admin = User::where('username', 'admin')->orWhere('email', 'admin@spl.com')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@spl.com',
                'phone' => '0811-0000-9999',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
                'is_active' => true,
            ]);
        } else {
            $admin->update([
                'username' => 'admin',
                'role' => 'admin',
            ]);
        }

        $agentBudi = User::firstOrCreate(
            ['username' => 'budi'],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@spl.com',
                'phone' => '0813-9876-5432',
                'role' => 'agent',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $agentSiti = User::firstOrCreate(
            ['username' => 'siti'],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti@spl.com',
                'phone' => '0815-5555-8888',
                'role' => 'agent',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // 2. MASTER BARANG DEMO
        $rawItems = [
            ['Khong Guan Classic Red 1600g', 115000, 102000, 'kaleng', 'dus', 6, 'Biskuit & Kue'],
            ['Monde Butter Cookies 454g', 78000, 69000, 'kaleng', 'dus', 6, 'Biskuit & Kue'],
            ['Wafer Tango Vanilla Kaleng 300g', 38000, 33000, 'kaleng', 'dus', 12, 'Biskuit & Kue'],
            ['Wafer Tango Cokelat Kaleng 300g', 38000, 33000, 'kaleng', 'dus', 12, 'Biskuit & Kue'],
            ['Wafer Roma Kelapa Kaleng 350g', 36000, 31000, 'kaleng', 'dus', 12, 'Biskuit & Kue'],
            ['Astor Wafer Stick Cokelat 330g', 42000, 36000, 'kaleng', 'dus', 12, 'Biskuit & Kue'],
            ['Nissin Wafer Chocolate 570g', 55000, 48000, 'kaleng', 'dus', 6, 'Biskuit & Kue'],
            ['Good Time Chocochip Cookies 149g', 24000, 20000, 'kaleng', 'dus', 12, 'Biskuit & Kue'],
            ['Oreo Biscuit Selection Box 286g', 46000, 40000, 'box', 'dus', 12, 'Biskuit & Kue'],
            ['Serena Cheese Cookies 454g', 68000, 60000, 'kaleng', 'dus', 6, 'Biskuit & Kue'],
            ['Sirup Marjan Boudoin Cocopandan 460ml', 24000, 20500, 'botol', 'dus', 12, 'Sirup & Minuman'],
            ['Sirup Marjan Boudoin Melon 460ml', 24000, 20500, 'botol', 'dus', 12, 'Sirup & Minuman'],
            ['Sirup ABC Squash Delight Jeruk 450ml', 16000, 13500, 'botol', 'dus', 12, 'Sirup & Minuman'],
            ['Sirup ABC Squash Delight Mangga 450ml', 16000, 13500, 'botol', 'dus', 12, 'Sirup & Minuman'],
            ['Susu Kental Manis Frisian Flag Gold 370g', 18000, 15500, 'kaleng', 'karton', 48, 'Sirup & Minuman'],
            ['Susu Kental Manis Indomilk Cokelat 370g', 15000, 13000, 'kaleng', 'karton', 48, 'Sirup & Minuman'],
            ['Nata De Coco Kara Crispy 1000g', 16500, 14000, 'pouch', 'dus', 12, 'Sirup & Minuman'],
            ['Nata De Coco Wong Coco 1000g', 18500, 15500, 'pouch', 'dus', 12, 'Sirup & Minuman'],
            ['Teh Botol Sosro Kotak 250ml', 4000, 3300, 'kotak', 'karton', 24, 'Sirup & Minuman'],
            ['Buavita Jus Jeruk 1000ml', 28000, 24000, 'kotak', 'dus', 12, 'Sirup & Minuman'],
            ['Minyak Goreng Sania 2 Liter', 36000, 32000, 'pouch', 'dus', 6, 'Sembako'],
            ['Minyak Goreng Tropical 2 Liter', 37000, 33000, 'botol', 'dus', 6, 'Sembako'],
            ['Beras Setra Ramos Super 5 Kg', 75000, 68000, 'karung', 'bal', 5, 'Sembako'],
            ['Gula Pasir Kristal Putih Gulaku 1 Kg', 18500, 16500, 'pcs', 'dus', 24, 'Sembako'],
            ['Gula Pasir Rose Brand 1 Kg', 18000, 16000, 'pcs', 'dus', 24, 'Sembako'],
            ['Tepung Terigu Segitiga Biru 1 Kg', 14000, 12000, 'pcs', 'dus', 12, 'Sembako'],
            ['Margarin Blue Band Serbaguna 200g', 11000, 9200, 'sachet', 'dus', 60, 'Sembako'],
            ['Kecap Manis Bango Refill 520ml', 24500, 21500, 'pouch', 'dus', 12, 'Sembako'],
            ['Saus Sambal ABC Extra Pedas 335ml', 17000, 14500, 'botol', 'dus', 24, 'Sembako'],
            ['Garam Dapur Beriodium Cap Kapal 250g', 3500, 2800, 'pcs', 'bal', 40, 'Sembako'],
            ['Kopi Kapal Api Spesial Mix 20s', 26000, 22000, 'bag', 'dus', 10, 'Kopi & Teh'],
            ['Kopi Luwak White Koffie 20s', 25000, 21000, 'bag', 'dus', 10, 'Kopi & Teh'],
            ['Teh Celup Sariwangi Kotak 50s', 14000, 11500, 'box', 'karton', 24, 'Kopi & Teh'],
            ['Teh Celup Sosro Kotak 50s', 12500, 10500, 'box', 'karton', 24, 'Kopi & Teh'],
            ['Cokelat Bubuk Van Houten 100g', 28000, 24000, 'box', 'dus', 12, 'Kopi & Teh'],
            ['Kurma Khalas Barari 500g', 42000, 35000, 'box', 'dus', 12, 'Khas Lebaran'],
            ['Kurma Tunis Madu Barari 500g', 52000, 44000, 'box', 'dus', 12, 'Khas Lebaran'],
            ['Permen Foxs Crystal Clear 180g', 12000, 9800, 'kaleng', 'dus', 24, 'Khas Lebaran'],
            ['Kacang Mede Oven Asin Gurih 250g', 48000, 41000, 'toples', 'dus', 12, 'Khas Lebaran'],
            ['Kacang Telur Garuda 220g', 16000, 13500, 'bungkus', 'dus', 20, 'Khas Lebaran'],
            ['Chocolatos Wafer Roll Kaleng 350g', 35000, 30000, 'kaleng', 'dus', 6, 'Camilan'],
            ['Twister Wafer Roll Vanila 300g', 32000, 27500, 'kaleng', 'dus', 6, 'Camilan'],
            ['Selamat Sandwich Wafer Chocolate 340g', 38000, 32500, 'kaleng', 'dus', 6, 'Camilan'],
            ['Mie Telur Cap Atom 200g', 6500, 5200, 'bungkus', 'dus', 20, 'Sembako'],
            ['Sarden ABC Saus Tomat 425g', 26000, 22500, 'kaleng', 'karton', 24, 'Sembako'],
            ['Kornet Sapi Pronas 340g', 34000, 29000, 'kaleng', 'dus', 24, 'Sembako'],
            ['Keju Kraft All In One 165g', 16500, 14000, 'box', 'dus', 24, 'Biskuit & Kue'],
            ['Meses Meises Ceres Classic 225g', 23000, 19500, 'box', 'dus', 12, 'Biskuit & Kue'],
            ['Agar-agar Swallow Globe Merah', 5000, 4100, 'sachet', 'pak', 12, 'Khas Lebaran'],
            ['Puding Nutrijell Cokelat 145g', 8500, 7000, 'box', 'dus', 12, 'Khas Lebaran'],
        ];

        $savedItems = [];
        foreach ($rawItems as $idx => $r) {
            $code = 'BRG-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
            $item = Item::firstOrCreate(['code' => $code], [
                'name' => $r[0],
                'unit_price' => $r[1],
                'cost_price' => $r[2],
                'base_unit' => $r[3],
                'wholesale_unit' => $r[4],
                'conversion_rate' => $r[5],
                'category' => $r[6],
                'is_active' => true,
            ]);
            $savedItems[$item->name] = $item;
        }

        // Master Item Kategori Uang
        $rawUangItems = [
            ['Uang Tunai Tabungan Lebaran Rp 1.500.000', 1500000, 1500000, 'amplop', 'amplop', 1, 'Uang'],
            ['Uang Tunai Tabungan Lebaran Rp 900.000', 900000, 900000, 'amplop', 'amplop', 1, 'Uang'],
            ['Uang Tunai THR Lebaran Rp 500.000', 500000, 500000, 'amplop', 'amplop', 1, 'Uang'],
        ];
        foreach ($rawUangItems as $idx => $r) {
            $code = 'UANG-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
            $item = Item::firstOrCreate(['code' => $code], [
                'name' => $r[0],
                'unit_price' => $r[1],
                'cost_price' => $r[2],
                'base_unit' => $r[3],
                'wholesale_unit' => $r[4],
                'conversion_rate' => $r[5],
                'category' => $r[6],
                'is_active' => true,
            ]);
            $savedItems[$item->name] = $item;
        }

        // Master Paket Parsel & Tabungan
        $pkgParselA = Package::firstOrCreate(['code' => 'PAR-001'], [
            'name' => 'Paket Parsel Mewah Sultan (Paket A)',
            'type' => 'parsel',
            'base_price' => 750000,
            'target_nominal' => 750000,
            'installment_count' => 30,
            'installment_amount' => 25000,
            'period_type' => 'mingguan',
            'description' => 'Paket parsel eksklusif lengkap biskuit kaleng premium, sirup marjan, kurma tunis, dan sembako berkualitas.',
            'image_path' => 'uploads/packages/sample_parsel_a.jpg',
            'is_active' => true,
        ]);
        $parselAItems = [
            'Khong Guan Classic Red 1600g' => 1,
            'Monde Butter Cookies 454g' => 1,
            'Sirup Marjan Boudoin Cocopandan 460ml' => 2,
            'Minyak Goreng Sania 2 Liter' => 2,
            'Beras Setra Ramos Super 5 Kg' => 1,
            'Gula Pasir Kristal Putih Gulaku 1 Kg' => 2,
            'Kurma Tunis Madu Barari 500g' => 1,
            'Kopi Kapal Api Spesial Mix 20s' => 1,
            'Teh Celup Sariwangi Kotak 50s' => 2,
            'Astor Wafer Stick Cokelat 330g' => 1,
            'Nata De Coco Wong Coco 1000g' => 2,
            'Kacang Mede Oven Asin Gurih 250g' => 1,
        ];
        foreach ($parselAItems as $name => $qty) {
            if (isset($savedItems[$name])) {
                PackageItem::firstOrCreate([
                    'package_id' => $pkgParselA->id,
                    'item_id' => $savedItems[$name]->id,
                ], [
                    'quantity' => $qty,
                    'is_bonus' => false,
                ]);
            }
        }

        $pkgTabunganA = Package::firstOrCreate(['code' => 'TAB-001'], [
            'name' => 'Paket Tabungan Lebaran Emas (Rp 1.5 Juta)',
            'type' => 'tabungan',
            'base_price' => 1500000,
            'target_nominal' => 1500000,
            'installment_count' => 30,
            'installment_amount' => 50000,
            'period_type' => 'mingguan',
            'description' => 'Nabung Rp 50.000/minggu. Saat Lebaran cair uang tunai Rp 1.500.000 + GRATIS BONUS BARANG (Khong Guan & Marjan).',
            'image_path' => 'uploads/packages/sample_tabungan_1.jpg',
            'is_active' => true,
        ]);
        if (isset($savedItems['Khong Guan Classic Red 1600g'])) {
            PackageItem::firstOrCreate([
                'package_id' => $pkgTabunganA->id,
                'item_id' => $savedItems['Khong Guan Classic Red 1600g']->id,
            ], [
                'quantity' => 1,
                'is_bonus' => true,
            ]);
        }
        if (isset($savedItems['Sirup Marjan Boudoin Cocopandan 460ml'])) {
            PackageItem::firstOrCreate([
                'package_id' => $pkgTabunganA->id,
                'item_id' => $savedItems['Sirup Marjan Boudoin Cocopandan 460ml']->id,
            ], [
                'quantity' => 2,
                'is_bonus' => true,
            ]);
        }

        $pkgEcer = Package::firstOrCreate(['code' => 'ECR-001'], [
            'name' => 'Paket Eceran Rakitan Mandiri (Custom Parsel)',
            'type' => 'ecer',
            'target_nominal' => 0,
            'installment_count' => 30,
            'installment_amount' => 0,
            'period_type' => 'mingguan',
            'description' => 'Konsumen bebas merakit parselnya sendiri dari daftar barang pilihan.',
            'is_active' => true,
        ]);

        // Demo Customers & Deposits
        $approvedDepositBudi = AgentDeposit::firstOrCreate(['deposit_number' => 'DEP-202610-0001'], [
            'agent_id' => $agentBudi->id,
            'total_amount' => 550000,
            'status' => 'approved',
            'agent_notes' => 'Setoran awal binaan Budi',
            'owner_notes' => 'Uang tunai Rp 550.000 sudah diterima di kantor oleh Bu Susi.',
            'submitted_at' => now()->subDays(14),
            'approved_at' => now()->subDays(13),
            'approved_by' => $owner->id,
        ]);

        $custAnisa = Customer::firstOrCreate(['code' => 'KNS-2610-001'], [
            'agent_id' => $agentBudi->id,
            'name' => 'Ibu Anisa Rahmawati',
            'phone' => '0812-1111-2222',
            'address' => 'Jl. Mawar No. 12 RT 02/04, Paseh',
            'notes' => 'Langganan setia tiap tahun',
        ]);
        $cpAnisa = CustomerPackage::firstOrCreate([
            'customer_id' => $custAnisa->id,
            'package_id' => $pkgParselA->id,
        ], [
            'agent_id' => $agentBudi->id,
            'start_date' => now()->subWeeks(10),
            'total_target' => 750000,
            'total_paid' => 250000,
            'status' => 'active',
            'fulfillment_status' => 'pending',
        ]);
        for ($w = 1; $w <= 30; $w++) {
            $isPaid = ($w <= 10);
            Payment::firstOrCreate([
                'customer_package_id' => $cpAnisa->id,
                'period_number' => $w,
            ], [
                'agent_id' => $agentBudi->id,
                'agent_deposit_id' => $isPaid ? $approvedDepositBudi->id : null,
                'amount' => 25000,
                'is_paid' => $isPaid,
                'paid_at' => $isPaid ? now()->subWeeks(11 - $w) : null,
                'is_locked' => $isPaid,
                'status' => $isPaid ? 'verified' : 'unpaid',
            ]);
        }

        $custHendra = Customer::firstOrCreate(['code' => 'KNS-2610-002'], [
            'agent_id' => $agentBudi->id,
            'name' => 'Bapak Hendra Wijaya',
            'phone' => '0857-3333-4444',
            'address' => 'Gg. Kenanga No. 5 RT 01/02',
            'notes' => 'Sering dinas luar kota',
        ]);
        $cpHendra = CustomerPackage::firstOrCreate([
            'customer_id' => $custHendra->id,
            'package_id' => $pkgTabunganA->id,
        ], [
            'agent_id' => $agentBudi->id,
            'start_date' => now()->subWeeks(10),
            'total_target' => 1500000,
            'total_paid' => 300000,
            'status' => 'active',
            'is_bonus_forfeited' => true,
            'forfeit_reason' => 'Tidak menyetor 4 minggu berturut-turut (Minggu 7 s.d 10 bolong). Sesuai aturan SPL, bonus barang hangus.',
            'consecutive_unpaid_weeks' => 4,
            'fulfillment_status' => 'pending',
        ]);
        for ($w = 1; $w <= 30; $w++) {
            $isPaid = ($w <= 6);
            Payment::firstOrCreate([
                'customer_package_id' => $cpHendra->id,
                'period_number' => $w,
            ], [
                'agent_id' => $agentBudi->id,
                'agent_deposit_id' => $isPaid ? $approvedDepositBudi->id : null,
                'amount' => 50000,
                'is_paid' => $isPaid,
                'paid_at' => $isPaid ? now()->subWeeks(11 - $w) : null,
                'is_locked' => $isPaid,
                'status' => $isPaid ? 'verified' : 'unpaid',
            ]);
        }

        $custDewi = Customer::firstOrCreate(['code' => 'KNS-2610-003'], [
            'agent_id' => $agentSiti->id,
            'name' => 'Ibu Dewi Sartika',
            'phone' => '0878-7777-9999',
            'address' => 'Komplek Griya Indah Blok B2 No. 8',
            'notes' => 'Mengajukan penyesuaian parsel sesuai saldo setorannya',
        ]);
        $custRahmat = Customer::firstOrCreate(['code' => 'KNS-2610-004'], [
            'agent_id' => $agentSiti->id,
            'name' => 'Bapak Rahmat Hidayat',
            'phone' => '0896-1234-5678',
            'address' => 'Kp. Babakan Desa Sukamaju',
            'notes' => 'Memilih sendiri barang parsel sesuai selera keluarga',
        ]);

        CatalogSetting::current();
    }
}

