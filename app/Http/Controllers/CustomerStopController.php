<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerPackage;
use App\Models\Customer;
use App\Models\Item;
use App\Models\PartialFulfillment;
use App\Models\PartialFulfillmentItem;

class CustomerStopController extends Controller
{
    /**
     * Agen atau Owner Mengajukan Stop Konsumen (Tidak Ingin Melanjutkan)
     */
    public function requestStop(Request $request, CustomerPackage $customerPackage)
    {
        $user = Auth::user();

        // Verifikasi hak akses: Agen pembina atau Owner/Admin
        if ($user->isAgent() && $customerPackage->agent_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengajukan stop untuk konsumen binaan agen lain.');
        }

        if ($customerPackage->stop_status === 'pending') {
            return back()->with('warning', 'Pengajuan stop untuk konsumen ini sudah dikirim sebelumnya dan sedang menunggu konfirmasi Owner.');
        }

        if ($customerPackage->stop_status === 'approved') {
            return back()->with('info', 'Konsumen ini sudah resmi dihentikan (disetujui Owner).');
        }

        $request->validate([
            'stop_reason' => 'required|string|min:3|max:1000',
        ], [
            'stop_reason.required' => 'Mohon isi alasan mengapa konsumen tidak ingin melanjutkan program.',
            'stop_reason.min' => 'Alasan minimal terdiri dari 3 karakter.',
        ]);

        $customerPackage->update([
            'stop_status' => 'pending',
            'stop_reason' => trim($request->stop_reason),
            'stop_requested_at' => now(),
            'stop_requested_by' => $user->id,
            'stop_rejection_reason' => null, // reset jika pernah ditolak sebelumnya
        ]);

        return back()->with('success', 'Pengajuan stop konsumen "' . ($customerPackage->customer?->name ?? 'Konsumen') . '" berhasil diajukan ke Owner. Menunggu konfirmasi dan penetapan alokasi barang dari Owner.');
    }

    /**
     * Agen Membatalkan Pengajuan Stop (Konsumen Berubah Pikiran Sebelum di-ACC Owner)
     */
    public function cancelRequest(CustomerPackage $customerPackage)
    {
        $user = Auth::user();

        if ($user->isAgent() && $customerPackage->agent_id !== $user->id) {
            abort(403, 'Anda tidak berhak membatalkan pengajuan ini.');
        }

        if ($customerPackage->stop_status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan dengan status menunggu konfirmasi yang dapat dibatalkan.');
        }

        $customerPackage->update([
            'stop_status' => 'none',
            'stop_reason' => null,
            'stop_requested_at' => null,
            'stop_requested_by' => null,
        ]);

        return back()->with('success', 'Pengajuan stop konsumen berhasil dibatalkan. Konsumen kembali aktif normal.');
    }

    /**
     * Owner: Daftar Pengajuan Stop Konsumen
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $query = CustomerPackage::with([
            'customer',
            'package',
            'agent',
            'stopRequestedBy',
            'stopApprovedBy',
            'partialFulfillment.items.item',
        ]);

        // Hitung badge counter
        $pendingCount = CustomerPackage::where('stop_status', 'pending')->count();
        $approvedCount = CustomerPackage::where('stop_status', 'approved')->count();
        $rejectedCount = CustomerPackage::where('stop_status', 'rejected')->count();

        if ($tab === 'pending') {
            $query->where('stop_status', 'pending');
        } elseif ($tab === 'approved') {
            $query->where('stop_status', 'approved');
        } elseif ($tab === 'rejected') {
            $query->where('stop_status', 'rejected');
        } else {
            $query->whereIn('stop_status', ['pending', 'approved', 'rejected']);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->whereHas('customer', function ($cq) use ($s) {
                    $cq->where('name', 'like', "%{$s}%")
                       ->orWhere('code', 'like', "%{$s}%")
                       ->orWhere('phone', 'like', "%{$s}%");
                })->orWhereHas('agent', function ($aq) use ($s) {
                    $aq->where('name', 'like', "%{$s}%");
                })->orWhere('stop_reason', 'like', "%{$s}%");
            });
        }

        $stopRequests = $query->latest('stop_requested_at')->paginate(15)->withQueryString();

        return view('owner.customer_stops.index', compact(
            'stopRequests',
            'tab',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * Owner: Form Penelaahan & Penetapan Alokasi Barang Pengganti Sesuai Total Setoran
     */
    public function review(CustomerPackage $customerPackage)
    {
        $customerPackage->load([
            'customer',
            'package.packageItems.item',
            'agent',
            'stopRequestedBy',
            'partialFulfillment.items.item',
        ]);

        $masterItems = Item::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $categories = Item::distinct()->whereNotNull('category')->pluck('category');

        return view('owner.customer_stops.review', compact('customerPackage', 'masterItems', 'categories'));
    }

    /**
     * Owner: Setujui (ACC) Pengajuan Stop & Alokasikan Barang Sesuai Nominal Setoran
     */
    public function approve(Request $request, CustomerPackage $customerPackage)
    {
        $user = Auth::user();

        $totalPaid = (float) $customerPackage->total_paid;

        $request->validate([
            'admin_fee' => 'nullable|numeric|min:0',
            'cash_refund' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ], [
            'items.required' => 'Wajib menentukan minimal 1 jenis barang yang diberikan kepada konsumen sesuai nominal setoran.',
            'items.min' => 'Wajib menentukan minimal 1 jenis barang yang diberikan kepada konsumen sesuai nominal setoran.',
            'items.*.item_id.required' => 'Pilih barang dari daftar komoditas.',
            'items.*.quantity.min' => 'Jumlah barang minimal 1.',
        ]);

        $adminFee = max(0, (float) $request->input('admin_fee', 0));
        $cashRefund = max(0, (float) $request->input('cash_refund', 0));
        $notes = $request->input('notes');

        DB::beginTransaction();
        try {
            // Hitung total nilai barang yang dipilih owner
            $totalItemsValue = 0;
            $itemsToCreate = [];

            foreach ($request->items as $itemInput) {
                if (empty($itemInput['item_id'])) continue;
                $item = Item::find($itemInput['item_id']);
                if (!$item) continue;

                $qty = max(1, (int) ($itemInput['quantity'] ?? 1));
                $unitPrice = isset($itemInput['unit_price']) && is_numeric($itemInput['unit_price']) 
                    ? (float) $itemInput['unit_price'] 
                    : (float) $item->unit_price;

                $sub = $qty * $unitPrice;
                $totalItemsValue += $sub;

                $itemsToCreate[] = [
                    'item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $sub,
                    'is_bonus' => false,
                ];
            }

            if (empty($itemsToCreate) && $totalPaid > 0) {
                return back()->withInput()->with('error', 'Gagal ACC: Owner wajib menentukan barang yang akan diserahkan kepada konsumen sesuai nominal setoran.');
            }

            // Hitung sisa pengembalian jika ada
            $remainingRefund = max(0, $totalPaid - $totalItemsValue - $adminFee);
            if ($cashRefund > 0) {
                $remainingRefund = $cashRefund;
            }

            // Hapus rekaman alokasi barang lama jika ada
            if ($customerPackage->partialFulfillment) {
                $customerPackage->partialFulfillment->items()->delete();
                $customerPackage->partialFulfillment->delete();
            }

            // Buat PartialFulfillment resmi untuk bukti serah terima barang stop
            $fulfillment = PartialFulfillment::create([
                'customer_package_id' => $customerPackage->id,
                'total_paid' => $totalPaid,
                'admin_fee' => $adminFee,
                'settlement_type' => 'goods',
                'cash_payout' => $remainingRefund,
                'total_items_value' => $totalItemsValue,
                'remaining_refund' => $remainingRefund,
                'adjusted_by' => $user->id,
                'notes' => $notes ?: ('Konsumen berhenti/stop atas pengajuan agen. Barang pengganti telah ditentukan Owner senilai Rp ' . number_format($totalItemsValue, 0, ',', '.') . ' sesuai total setoran masuk Rp ' . number_format($totalPaid, 0, ',', '.')),
            ]);

            foreach ($itemsToCreate as $it) {
                $it['partial_fulfillment_id'] = $fulfillment->id;
                PartialFulfillmentItem::create($it);
            }

            // Update status paket konsumen menjadi berhenti & disahkan
            $customerPackage->status = 'canceled';
            $customerPackage->stop_status = 'approved';
            $customerPackage->stop_approved_at = now();
            $customerPackage->stop_approved_by = $user->id;
            $customerPackage->fulfillment_status = 'partial';
            $customerPackage->fulfillment_decision = 'stopped_goods';
            $customerPackage->decided_at = now();
            $customerPackage->decided_by = $user->id;
            $customerPackage->cancellation_fee = $adminFee;
            $customerPackage->refund_amount = $remainingRefund;
            $customerPackage->fulfillment_notes = 'Pemberhentian Konsumen Telah di-ACC Owner: Diberikan ' . count($itemsToCreate) . ' macam barang senilai Rp ' . number_format($totalItemsValue, 0, ',', '.') . ($remainingRefund > 0 ? (' + Kembalian Tunai: Rp ' . number_format($remainingRefund, 0, ',', '.')) : '');
            $customerPackage->save();

            // Kunci semua jadwal pembayaran yang belum lunas
            $customerPackage->payments()->where('is_paid', false)->update([
                'is_locked' => true,
            ]);

            // Jika konsumen tidak memiliki paket aktif lain, tandai is_active false
            $hasOtherActive = CustomerPackage::where('customer_id', $customerPackage->customer_id)
                ->where('id', '!=', $customerPackage->id)
                ->where('status', 'active')
                ->exists();

            if (!$hasOtherActive && $customerPackage->customer) {
                $customerPackage->customer->update(['is_active' => false]);
            }

            DB::commit();

            return redirect()->route('owner.customer-stops.receipt', $customerPackage)
                ->with('success', 'PENGAJUAN STOP KONSUMEN BERHASIL DI-ACC! Barang pengganti telah resmi dialokasikan sesuai total setoran konsumen. Silakan cetak Bukti Serah Terima Barang berikut.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses ACC: ' . $e->getMessage());
        }
    }

    /**
     * Owner: Tolak Pengajuan Stop Konsumen
     */
    public function reject(Request $request, CustomerPackage $customerPackage)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:3|max:1000',
        ], [
            'rejection_reason.required' => 'Mohon isi alasan penolakan pengajuan stop.',
        ]);

        $customerPackage->update([
            'stop_status' => 'rejected',
            'stop_rejection_reason' => trim($request->rejection_reason),
            'status' => 'active', // Tetap aktif
        ]);

        return back()->with('warning', 'Pengajuan stop konsumen ditolak. Status konsumen dikembalikan ke aktif normal.');
    }

    /**
     * Lembar Cetak / Tanda Terima Serah Terima Barang Pemberhentian Konsumen
     */
    public function receipt(CustomerPackage $customerPackage)
    {
        $customerPackage->load([
            'customer',
            'package',
            'agent',
            'stopRequestedBy',
            'stopApprovedBy',
            'partialFulfillment.items.item',
        ]);

        if (!$customerPackage->partialFulfillment) {
            return redirect()->route('owner.customer-stops.index')
                ->with('error', 'Belum ada rincian alokasi barang untuk konsumen ini.');
        }

        $appSetting = \App\Models\CatalogSetting::current();

        return view('owner.customer_stops.receipt', compact('customerPackage', 'appSetting'));
    }
}
