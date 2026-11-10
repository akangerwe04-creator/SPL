<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AgentDepositController;
use App\Http\Controllers\MiniPosController;
use App\Http\Controllers\InventoryPlannerController;
use App\Http\Controllers\SocialFeedController;
use App\Http\Controllers\CatalogGeneratorController;
use App\Http\Controllers\DataResetController;
use App\Http\Controllers\AgentManagementController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\PackagingController;
use App\Http\Controllers\BookClosingController;

// Root Redirector
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isOwnerOrAdmin()
            ? redirect()->route('owner.dashboard')
            : redirect()->route('agent.feed');
    }
    return redirect()->route('login');
});

// PWA Manifest Dinamis & Badging API
Route::get('/manifest.json', [AppSettingController::class, 'manifest'])->name('manifest');
Route::get('/api/notifications/badge-count', [AppSettingController::class, 'getBadgeCount'])->name('api.badge-count');

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // 1. Sosial Media Internal (Instagram Mode)
    Route::get('/social', [SocialFeedController::class, 'index'])->name('social.feed');
    Route::post('/social/post', [SocialFeedController::class, 'store'])->name('social.store');
    Route::post('/social/post/{post}/comment', [SocialFeedController::class, 'storeComment'])->name('social.comment');
    Route::post('/social/post/{post}/like', [SocialFeedController::class, 'like'])->name('social.like');
    Route::delete('/social/post/{post}', [SocialFeedController::class, 'destroy'])->name('social.destroy');

    // 2. Agen Alias Route
    Route::get('/agent/feed', fn() => redirect()->route('social.feed'))->name('agent.feed');

    // 3. Manajemen Konsumen
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::post('/customers/package/{customerPackage}/cancel', [CustomerController::class, 'cancelPackage'])->name('customers.package.cancel');

    // 4. Smart Payment Checklist
    Route::get('/payments/{customerPackage}/checklist', [PaymentController::class, 'checklist'])->name('payments.checklist');
    Route::post('/payments/{payment}/toggle', [PaymentController::class, 'toggle'])->name('payments.toggle');

    // 5. Keranjang Setoran Agen
    Route::get('/agent/cart', [AgentDepositController::class, 'currentCart'])->name('agent.cart');
    Route::post('/agent/cart/{deposit}/submit', [AgentDepositController::class, 'submitCart'])->name('agent.cart.submit');
    Route::get('/agent/deposits/history', [AgentDepositController::class, 'agentHistory'])->name('agent.deposits.history');

    // 6. OWNER ONLY ROUTES (7 MENU TERSTRUKTUR)
    Route::middleware(['role:owner'])->group(function () {
        // Menu 1: Dashboard (Dasbor Finansial)
        Route::get('/owner/dashboard', [DashboardController::class, 'index'])->name('owner.dashboard');

        // Menu 2: Menu Agen (Tim Lapangan & Konsumen Binaan)
        Route::get('/owner/agents', [AgentManagementController::class, 'index'])->name('owner.agents.index');
        Route::get('/owner/agents/report', [AgentManagementController::class, 'report'])->name('owner.agents.report');
        Route::get('/owner/agents/report/print', [AgentManagementController::class, 'reportPrint'])->name('owner.agents.report.print');
        Route::get('/owner/agents/report/export', [AgentManagementController::class, 'reportExportXlsx'])->name('owner.agents.report.export');
        Route::get('/owner/agents/export-accounts', [AgentManagementController::class, 'exportAccountsXlsx'])->name('owner.agents.export-accounts');
        Route::get('/owner/agents/import-template', [AgentManagementController::class, 'downloadImportTemplate'])->name('owner.agents.import.template');
        Route::post('/owner/agents/import', [AgentManagementController::class, 'import'])->name('owner.agents.import');
        Route::post('/owner/agents', [AgentManagementController::class, 'store'])->name('owner.agents.store');
        Route::put('/owner/agents/{agent}', [AgentManagementController::class, 'update'])->name('owner.agents.update');
        Route::delete('/owner/agents/{agent}', [AgentManagementController::class, 'destroy'])->name('owner.agents.destroy');

        // Menu 3: Data Barang
        Route::post('/owner/items/quick-uang', [ItemController::class, 'quickStoreUang'])->name('items.quick-uang');
        Route::resource('items', ItemController::class);

        // Menu Tambahan: Data Kemasan (Koper, Keranjang, Box, dll.)
        Route::resource('owner/packagings', PackagingController::class)->names([
            'index' => 'owner.packagings.index',
            'create' => 'owner.packagings.create',
            'store' => 'owner.packagings.store',
            'edit' => 'owner.packagings.edit',
            'update' => 'owner.packagings.update',
            'destroy' => 'owner.packagings.destroy',
        ]);
        Route::get('/api/packagings', [PackagingController::class, 'apiList'])->name('api.packagings');

        // Menu 4: Menu Paket (Parsel, Tabungan, Eceran)
        Route::get('/packages/import-template', [PackageController::class, 'downloadImportTemplate'])->name('packages.import.template');
        Route::post('/packages/import', [PackageController::class, 'import'])->name('packages.import');
        Route::resource('packages', PackageController::class);

        // Menu 5: Rekonsiliasi Agen (Pengesahan Setoran Kas)
        Route::get('/owner/deposits', [AgentDepositController::class, 'ownerIndex'])->name('owner.deposits.index');
        Route::get('/owner/deposits/{deposit}', [AgentDepositController::class, 'ownerShow'])->name('owner.deposits.show');
        Route::post('/owner/deposits/{deposit}/approve', [AgentDepositController::class, 'ownerApprove'])->name('owner.deposits.approve');
        Route::post('/owner/deposits/{deposit}/reject', [AgentDepositController::class, 'ownerReject'])->name('owner.deposits.reject');

        // Menu 6: Belanja Barang (Kalkulator Belanja Grosir / Kulakan)
        Route::get('/owner/inventory-planner', [InventoryPlannerController::class, 'index'])->name('inventory.planner');
        Route::get('/owner/inventory-planner/print', [InventoryPlannerController::class, 'printView'])->name('inventory.print');

        // Menu 7: Tutup Buku & Keputusan Pemenuhan Musim Parsel (Hak Akses Owner & Admin)
        Route::get('/owner/book-closing', [BookClosingController::class, 'index'])->name('owner.book-closing.index');
        Route::post('/owner/book-closing/close', [BookClosingController::class, 'closeBook'])->name('owner.book-closing.close');
        Route::post('/owner/book-closing/reopen', [BookClosingController::class, 'reopenBook'])->name('owner.book-closing.reopen');
        Route::get('/owner/book-closing/{customerPackage}/decision', [BookClosingController::class, 'decision'])->name('owner.book-closing.decision');
        Route::post('/owner/book-closing/{customerPackage}/decision', [BookClosingController::class, 'storeDecision'])->name('owner.book-closing.store-decision');
        Route::post('/owner/book-closing/bulk-approve-safe', [BookClosingController::class, 'bulkApproveSafe'])->name('owner.book-closing.bulk-approve-safe');
        Route::post('/owner/book-closing/update-thresholds', [BookClosingController::class, 'updatePackageThresholds'])->name('owner.book-closing.update-thresholds');
        Route::get('/owner/book-closing/{customerPackage}/receipt', [BookClosingController::class, 'printDecisionReceipt'])->name('owner.book-closing.receipt');

        // Penyesuaian Parsel & Tabungan Magol / Partial Fulfillment (Mini-POS Kasir)
        Route::get('/owner/mini-pos', [MiniPosController::class, 'index'])->name('mini-pos.index');
        Route::get('/owner/mini-pos/{customerPackage}', [MiniPosController::class, 'pos'])->name('mini-pos.pos');
        Route::post('/owner/mini-pos/{customerPackage}/adjust', [MiniPosController::class, 'storeAdjustment'])->name('mini-pos.adjust');
        Route::post('/owner/mini-pos/{customerPackage}/adjust-tabungan', [MiniPosController::class, 'storeTabunganAdjustment'])->name('mini-pos.adjust.tabungan');
        Route::get('/owner/mini-pos/receipt/{fulfillment}', [MiniPosController::class, 'receipt'])->name('mini-pos.receipt');

        // Cetak Brosur Katalog
        Route::get('/owner/catalog', [CatalogGeneratorController::class, 'index'])->name('catalog.index');
        Route::post('/owner/catalog/settings', [CatalogGeneratorController::class, 'updateSettings'])->name('catalog.settings.update');
        Route::get('/owner/catalog/print/parsel', [CatalogGeneratorController::class, 'printParsel'])->name('catalog.print.parsel');
        Route::get('/owner/catalog/print/tabungan', [CatalogGeneratorController::class, 'printTabungan'])->name('catalog.print.tabungan');
        Route::get('/owner/catalog/print/ecer', [CatalogGeneratorController::class, 'printEcer'])->name('catalog.print.ecer');

        // Menu 7: Pengaturan Aplikasi (Nama, Logo, Alamat Owner, serta Fitur Reset Data)
        Route::get('/owner/settings', [AppSettingController::class, 'index'])->name('owner.settings.index');
        Route::post('/owner/settings/profile', [AppSettingController::class, 'updateProfile'])->name('owner.settings.profile');

        // Fitur Reset Data Sistem
        Route::get('/owner/reset-data', [DataResetController::class, 'index'])->name('owner.reset.index');
        Route::post('/owner/reset-data', [DataResetController::class, 'reset'])->name('owner.reset.execute');

        // Fitur Cadangan Database (Backup & Restore)
        Route::get('/owner/backup/download-current', [BackupController::class, 'downloadCurrent'])->name('owner.backup.download-current');
        Route::post('/owner/backup/create', [BackupController::class, 'createSnapshot'])->name('owner.backup.create');
        Route::get('/owner/backup/download/{filename}', [BackupController::class, 'downloadFile'])->name('owner.backup.download');
        Route::post('/owner/backup/restore', [BackupController::class, 'restore'])->name('owner.backup.restore');
        Route::delete('/owner/backup/{filename}', [BackupController::class, 'destroy'])->name('owner.backup.destroy');

        // Auto-Cleanup Trigger
        Route::post('/owner/cleanup-posts', [SocialFeedController::class, 'runCleanup'])->name('social.cleanup');
    });

    // 7. ADMIN ONLY ROUTES (MENU MANAJEMEN AKUN TOTAL - TERSEMBUNYI DARI OWNER)
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::put('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });
});
