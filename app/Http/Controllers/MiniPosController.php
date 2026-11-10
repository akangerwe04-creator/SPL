<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerPackage;
use App\Models\Item;
use App\Models\PartialFulfillment;
use App\Models\PartialFulfillmentItem;

class MiniPosController extends Controller
{
    /**
     * Daftar Konsumen Belum Lunas / Menunggak
     */
    public function index(Request $request)
    {
        $query = CustomerPackage::with(['customer', 'package', 'agent', 'partialFulfillment'])
            ->where('status', '!=', 'canceled');

        if ($request->filled('filter')) {
            if ($request->filter === 'adjusted') {
                $query->where('fulfillment_status', 'partial');
            } elseif ($request->filter === 'unadjusted') {
                $query->where('fulfillment_status', 'pending')
                      ->whereColumn('total_paid', '<', 'total_target');
            }
        } else {
            // Default: tampilkan yang belum lunas
            $query->whereColumn('total_paid', '<', 'total_target');
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('customer', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $customerPackages = $query->orderBy('consecutive_unpaid_weeks', 'desc')->paginate(15)->withQueryString();

        return view('mini_pos.index', compact('customerPackages'));
    }

    /**
     * Halaman Kasir Mini-POS Penyesuaian (Barang atau Tabungan Magol)
     */
    public function pos(CustomerPackage $customerPackage)
    {
        $customerPackage->load([
            'customer',
            'package.packageItems.item',
            'agent',
            'partialFulfillment.items.item',
        ]);

        // Jika konsumen tabungan magol, arahkan ke kasir penyesuaian tabungan (input nominal manual & potongan admin)
        if ($customerPackage->isTabungan()) {
            return view('mini_pos.pos_tabungan', compact('customerPackage'));
        }

        // Konsumen paket barang (parsel / ecer)
        $items = Item::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $categories = Item::distinct()->whereNotNull('category')->pluck('category');

        return view('mini_pos.pos', compact('customerPackage', 'items', 'categories'));
    }

    /**
     * Simpan Penyesuaian Barang Pengganti (Fulfillment Paket Parsel / Ecer Magol)
     */
    public function storeAdjustment(Request $request, CustomerPackage $customerPackage)
    {
        $owner = Auth::user();

        $request->validate([
            'admin_fee' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $totalPaid = (float) $customerPackage->total_paid;
        $adminFee = max(0, (float) $request->input('admin_fee', 0));

        if ($adminFee > $totalPaid) {
            return back()->withInput()->with('error', 'Potongan admin (Rp ' . number_format($adminFee, 0, ',', '.') . 
                ') melebihi total saldo setoran konsumen (Rp ' . number_format($totalPaid, 0, ',', '.') . ')!');
        }

        $availableBudget = max(0, $totalPaid - $adminFee);
        $totalItemsValue = 0;
        $itemsToSave = [];

        foreach ($request->items as $itemInput) {
            $item = Item::find($itemInput['item_id']);
            if ($item) {
                $qty = (int) $itemInput['quantity'];
                $subtotal = $item->unit_price * $qty;
                $totalItemsValue += $subtotal;
                $itemsToSave[] = [
                    'item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        // Validasi jika total nilai barang melebihi anggaran yang tersedia (setelah dipotong admin)
        if ($totalItemsValue > $availableBudget) {
            return back()->withInput()->with('error', 'TOTAL NILAI BARANG (Rp ' . number_format($totalItemsValue, 0, ',', '.') . 
                ') MELEBIHI ANGGARAN BELANJA SETELAH POTONGAN ADMIN (Rp ' . number_format($availableBudget, 0, ',', '.') . ')! Penyesuaian ditolak.');
        }

        DB::beginTransaction();
        try {
            // Hapus fulfillment lama jika sebelumnya sudah pernah dibuat
            if ($customerPackage->partialFulfillment) {
                $customerPackage->partialFulfillment->items()->delete();
                $customerPackage->partialFulfillment->delete();
            }

            $remainingRefund = max(0, $totalPaid - $totalItemsValue - $adminFee);

            $fulfillment = PartialFulfillment::create([
                'customer_package_id' => $customerPackage->id,
                'total_paid' => $totalPaid,
                'admin_fee' => $adminFee,
                'settlement_type' => 'goods',
                'cash_payout' => 0,
                'total_items_value' => $totalItemsValue,
                'remaining_refund' => $remainingRefund,
                'adjusted_by' => $owner->id,
                'notes' => $request->notes ?: 'Penyesuaian paket parsel magol dengan barang pengganti.',
            ]);

            foreach ($itemsToSave as $row) {
                $row['partial_fulfillment_id'] = $fulfillment->id;
                PartialFulfillmentItem::create($row);
            }

            // Perbarui status pemenuhan customer package
            $customerPackage->fulfillment_status = 'partial';
            $customerPackage->fulfillment_notes = 'Telah disesuaikan via Mini-POS dengan ' . count($itemsToSave) . ' jenis barang pengganti senilai Rp ' . number_format($totalItemsValue, 0, ',', '.') . 
                ($adminFee > 0 ? ' (Potongan Admin SPL: Rp ' . number_format($adminFee, 0, ',', '.') . ')' : '') .
                ($remainingRefund > 0 ? ', Sisa Refund Tunai: Rp ' . number_format($remainingRefund, 0, ',', '.') : '');
            $customerPackage->save();

            DB::commit();

            return redirect()->route('mini-pos.receipt', $fulfillment)
                ->with('success', 'Penyesuaian barang berhasil disimpan. Siap dicetak sebagai Berita Acara Serah Terima.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan penyesuaian: ' . $e->getMessage());
        }
    }

    /**
     * Simpan Penyesuaian Dana Tabungan Magol (Manual Input Nominal & Potongan Admin)
     */
    public function storeTabunganAdjustment(Request $request, CustomerPackage $customerPackage)
    {
        $owner = Auth::user();

        $request->validate([
            'nominal_received' => 'required|numeric|min:0',
            'admin_fee' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $totalPaid = (float) $customerPackage->total_paid;
        $nominalReceived = (float) $request->input('nominal_received', 0);
        $adminFee = max(0, (float) $request->input('admin_fee', 0));

        // Validasi: Nominal yang diterima + Potongan Admin tidak boleh melebihi total tabungan masuk
        if (($nominalReceived + $adminFee) > $totalPaid) {
            return back()->withInput()->with('error', 'Jumlah nominal yang diterima (Rp ' . number_format($nominalReceived, 0, ',', '.') . 
                ') ditambah potongan admin (Rp ' . number_format($adminFee, 0, ',', '.') . 
                ') MELEBIHI TOTAL SALDO TABUNGAN MASUK (Rp ' . number_format($totalPaid, 0, ',', '.') . ')! Silakan sesuaikan kembali.');
        }

        DB::beginTransaction();
        try {
            // Hapus fulfillment lama jika ada
            if ($customerPackage->partialFulfillment) {
                $customerPackage->partialFulfillment->items()->delete();
                $customerPackage->partialFulfillment->delete();
            }

            $remainingRefund = max(0, $totalPaid - $nominalReceived - $adminFee);

            $fulfillment = PartialFulfillment::create([
                'customer_package_id' => $customerPackage->id,
                'total_paid' => $totalPaid,
                'admin_fee' => $adminFee,
                'settlement_type' => 'cash',
                'cash_payout' => $nominalReceived,
                'total_items_value' => 0,
                'remaining_refund' => $remainingRefund,
                'adjusted_by' => $owner->id,
                'notes' => $request->notes ?: 'Penyelesaian penyerahan dana tabungan magol kepada konsumen.',
            ]);

            // Perbarui status pemenuhan customer package
            $customerPackage->fulfillment_status = 'partial';
            $customerPackage->fulfillment_notes = 'Penyesuaian Tabungan Magol: Dana tunai diserahkan Rp ' . number_format($nominalReceived, 0, ',', '.') . 
                ($adminFee > 0 ? ' (Potongan Admin SPL: Rp ' . number_format($adminFee, 0, ',', '.') . ')' : '') .
                ($remainingRefund > 0 ? ', Saldo Ditahan: Rp ' . number_format($remainingRefund, 0, ',', '.') : '');
            $customerPackage->save();

            DB::commit();

            return redirect()->route('mini-pos.receipt', $fulfillment)
                ->with('success', 'Penyesuaian dana tabungan magol berhasil diproses. Siap dicetak sebagai Kwitansi Serah Terima Dana.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memproses penyesuaian tabungan: ' . $e->getMessage());
        }
    }

    /**
     * Nota / Berita Acara Serah Terima Barang Pengganti / Kwitansi Tabungan Siap Cetak
     */
    public function receipt(PartialFulfillment $fulfillment)
    {
        $fulfillment->load([
            'customerPackage.customer',
            'customerPackage.package',
            'customerPackage.agent',
            'adjuster',
            'items.item',
        ]);

        return view('mini_pos.receipt', compact('fulfillment'));
    }
}

