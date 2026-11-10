<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Item;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\CustomerCustomItem;
use App\Models\AgentDeposit;
use App\Models\Payment;
use App\Models\PartialFulfillment;
use App\Models\PartialFulfillmentItem;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostLike;

class DataResetController extends Controller
{
    /**
     * Tampilkan halaman formulir Reset Data untuk Owner
     */
    public function index()
    {
        $counts = [
            'transactions' => [
                'payments' => Payment::count(),
                'paid_payments' => Payment::where('is_paid', true)->count(),
                'deposits' => AgentDeposit::count(),
            ],
            'fulfillments' => [
                'total' => PartialFulfillment::count(),
            ],
            'customers' => [
                'customers' => Customer::count(),
                'packages' => CustomerPackage::count(),
            ],
            'social' => [
                'posts' => Post::count(),
                'comments' => Comment::count(),
            ],
            'packages' => [
                'total' => Package::count(),
            ],
            'items' => [
                'total' => Item::count(),
            ],
            'agents' => [
                'total' => User::where('role', 'agent')->count(),
            ],
        ];

        return view('owner.reset.index', compact('counts'));
    }

    /**
     * Eksekusi reset data berdasarkan checkbox yang dipilih Owner
     */
    public function reset(Request $request)
    {
        $request->validate([
            'confirm_text' => 'required|string',
            'targets' => 'nullable|array',
            'reset_all' => 'nullable|boolean',
            'reseed_demo' => 'nullable|boolean',
        ]);

        // Verifikasi kata kunci konfirmasi 'RESET' atau password akun owner
        $confirmInput = trim($request->confirm_text);
        $isTextValid = (strtoupper($confirmInput) === 'RESET');
        $isPasswordValid = Hash::check($confirmInput, auth()->user()->password);

        if (!$isTextValid && !$isPasswordValid) {
            return back()->with('error', 'Konfirmasi keamanan gagal! Ketik kata "RESET" atau masukkan password akun Anda untuk melanjutkan.');
        }

        $resetAll = $request->boolean('reset_all');
        $targets = $request->input('targets', []);

        if (!$resetAll && empty($targets)) {
            return back()->with('error', 'Silakan pilih minimal satu kategori data yang ingin direset.');
        }

        $summary = [];

        try {
            // Disable foreign key checks sementara untuk kelancaran pembersihan data
            $isSqlite = (DB::getDriverName() === 'sqlite');
            if ($isSqlite) {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            }

            DB::transaction(function () use ($resetAll, $targets, &$summary) {

                // 1. JIKA RESET SEMUA DATA (FACTORY RESET)
                if ($resetAll) {
                    PartialFulfillmentItem::query()->delete();
                    PartialFulfillment::query()->delete();
                    Payment::query()->delete();
                    AgentDeposit::query()->delete();
                    CustomerCustomItem::query()->delete();
                    CustomerPackage::query()->delete();
                    Customer::query()->delete();

                    // Bersihkan gambar postingan feed
                    $posts = Post::all();
                    foreach ($posts as $post) {
                        if ($post->image_path && file_exists(public_path($post->image_path))) {
                            if (!str_contains($post->image_path, 'sample_')) {
                                @unlink(public_path($post->image_path));
                            }
                        }
                    }
                    if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
                        PostLike::query()->delete();
                    }
                    Comment::query()->delete();
                    Post::query()->delete();

                    // Bersihkan master paket
                    $packages = Package::all();
                    foreach ($packages as $pkg) {
                        if ($pkg->image_path && file_exists(public_path($pkg->image_path))) {
                            if (!str_contains($pkg->image_path, 'sample_')) {
                                @unlink(public_path($pkg->image_path));
                            }
                        }
                    }
                    PackageItem::query()->delete();
                    Package::query()->delete();

                    // Bersihkan master item
                    Item::query()->delete();

                    // Bersihkan akun agen (Akun OWNER & ADMIN TETAP AMAN)
                    User::whereNotIn('role', ['owner', 'admin'])->delete();

                    $summary[] = 'Semua data operasional, master data, dan akun agen telah direset total.';
                    return;
                }

                // 2. JIKA RESET SEBAGIAN (BERDASARKAN CHECKBOX PILIHAN)

                // A. Reset Transaksi Pembayaran & Setoran Agen
                if (in_array('transactions', $targets)) {
                    AgentDeposit::query()->delete();

                    // Jika konsumen tidak ikut dihapus, kembalikan status checklist ke unpaid
                    if (!in_array('customers', $targets)) {
                        Payment::query()->update([
                            'is_paid' => false,
                            'paid_at' => null,
                            'agent_deposit_id' => null,
                            'status' => 'unpaid',
                            'is_locked' => false,
                        ]);
                        CustomerPackage::query()->update([
                            'total_paid' => 0,
                            'is_bonus_forfeited' => false,
                            'forfeit_reason' => null,
                            'consecutive_unpaid_weeks' => 0,
                        ]);
                    } else {
                        Payment::query()->delete();
                    }
                    $summary[] = 'Transaksi pembayaran cicilan & setoran agen berhasil direset.';
                }

                // B. Reset Mini-POS & Penyerahan Parsel
                if (in_array('fulfillments', $targets)) {
                    PartialFulfillmentItem::query()->delete();
                    PartialFulfillment::query()->delete();
                    CustomerPackage::query()->update([
                        'fulfillment_status' => 'pending',
                        'fulfillment_notes' => null,
                    ]);
                    $summary[] = 'Riwayat penyerahan parsel Mini-POS berhasil dibersihkan.';
                }

                // C. Reset Data Konsumen & Pendaftaran Paket
                if (in_array('customers', $targets)) {
                    PartialFulfillmentItem::query()->delete();
                    PartialFulfillment::query()->delete();
                    Payment::query()->delete();
                    CustomerCustomItem::query()->delete();
                    CustomerPackage::query()->delete();
                    Customer::query()->delete();
                    $summary[] = 'Data seluruh konsumen & paket terdaftar berhasil dihapus.';
                }

                // D. Reset Postingan & Komentar Sosial Feed
                if (in_array('social', $targets)) {
                    $posts = Post::all();
                    foreach ($posts as $post) {
                        if ($post->image_path && file_exists(public_path($post->image_path))) {
                            if (!str_contains($post->image_path, 'sample_')) {
                                @unlink(public_path($post->image_path));
                            }
                        }
                    }
                    if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
                        PostLike::query()->delete();
                    }
                    Comment::query()->delete();
                    Post::query()->delete();
                    $summary[] = 'Postingan dan komentar feed komunitas berhasil dibersihkan.';
                }

                // E. Reset Master Paket Parsel & Tabungan
                if (in_array('packages', $targets)) {
                    // Paket yang diambil konsumen juga harus dihapus jika belum
                    CustomerCustomItem::query()->delete();
                    CustomerPackage::query()->delete();
                    Payment::query()->delete();
                    PartialFulfillmentItem::query()->delete();
                    PartialFulfillment::query()->delete();

                    $packages = Package::all();
                    foreach ($packages as $pkg) {
                        if ($pkg->image_path && file_exists(public_path($pkg->image_path))) {
                            if (!str_contains($pkg->image_path, 'sample_')) {
                                @unlink(public_path($pkg->image_path));
                            }
                        }
                    }
                    PackageItem::query()->delete();
                    Package::query()->delete();
                    $summary[] = 'Master paket parsel & tabungan berhasil dihapus.';
                }

                // F. Reset Master Barang
                if (in_array('items', $targets)) {
                    PartialFulfillmentItem::query()->delete();
                    CustomerCustomItem::query()->delete();
                    PackageItem::query()->delete();
                    Item::query()->delete();
                    $summary[] = 'Daftar master barang berhasil dikosongkan.';
                }

                // G. Reset Akun Agen Lapangan
                if (in_array('agents', $targets)) {
                    // Hapus data yang terikat agen jika ada
                    Customer::query()->delete();
                    AgentDeposit::query()->delete();
                    User::where('role', 'agent')->delete();
                    $summary[] = 'Akun agen lapangan berhasil dihapus (Akun Owner tetap tersimpan).';
                }
            });

            // Re-enable foreign key checks
            if ($isSqlite) {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            }

            // Opsi: Jalankan kembali database seeder jika dipilih
            if ($request->boolean('reseed_demo')) {
                Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DemoDataSeeder', '--force' => true]);
                $summary[] = 'Data sampel awal (Demo Seeder) berhasil dimuat kembali.';
            }

            // Pastikan akun owner permanen tetap utuh dan aktif setelah reset
            \App\Models\User::ensurePermanentOwner();

            // Pastikan logo permanen tetap terjaga aman dan tidak hilang saat reset
            \App\Models\CatalogSetting::ensurePermanentLogo();
            $setting = \App\Models\CatalogSetting::first();
            if ($setting && (!$setting->logo_path || !file_exists(public_path($setting->logo_path)))) {
                if (file_exists(public_path('permanent/logo.png'))) {
                    $setting->update(['logo_path' => 'permanent/logo.png']);
                }
            }
            if ($setting) {
                \App\Http\Controllers\AppSettingController::syncPwaAssetsAndManifest($setting);
            }

            $message = implode(' ', $summary);
            return redirect()->route('owner.reset.index')->with('success', $message);

        } catch (\Throwable $e) {
            // Pastikan foreign keys aktif kembali walau terjadi exception
            if ($isSqlite) {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            }

            return back()->with('error', 'Gagal mereset data: ' . $e->getMessage());
        }
    }
}

