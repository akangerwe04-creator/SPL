<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AgentDeposit;
use App\Models\User;

class AgentDepositController extends Controller
{
    /**
     * Agen: Halaman Keranjang Setoran Berjalan (Running Total & Rincian)
     */
    public function currentCart()
    {
        $user = Auth::user();
        $cart = AgentDeposit::getOrCreateActiveCart($user->id);
        $cart->load(['payments.customerPackage.customer', 'payments.customerPackage.package']);
        $cart->recalculateTotal();

        return view('deposits.cart', compact('cart'));
    }

    /**
     * Agen: Kirim / Ajukan Keranjang Setoran ke Owner
     */
    public function submitCart(Request $request, AgentDeposit $deposit)
    {
        $user = Auth::user();

        if ($deposit->agent_id !== $user->id) {
            abort(403);
        }

        if ($deposit->status !== 'pending') {
            return back()->with('error', 'Keranjang ini sudah pernah diajukan.');
        }

        if ($deposit->payments()->count() === 0 || $deposit->total_amount <= 0) {
            return back()->with('error', 'Keranjang setoran masih kosong (Rp 0). Silakan lakukan ceklis tagihan terlebih dahulu.');
        }

        $deposit->submit($request->input('notes'));

        // Otomatis siapkan keranjang baru Rp 0 untuk aktivitas tagihan berikutnya
        AgentDeposit::getOrCreateActiveCart($user->id);

        return redirect()->route('agent.deposits.history')
            ->with('success', 'Setoran sebesar ' . $deposit->formatted_total . ' berhasil diajukan ke Ibu Susi / Owner. Menunggu pengesahan.');
    }

    /**
     * Agen: Riwayat Setoran Lapangan
     */
    public function agentHistory()
    {
        $user = Auth::user();
        $deposits = AgentDeposit::where('agent_id', $user->id)
            ->withCount('payments')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $activeCart = AgentDeposit::getOrCreateActiveCart($user->id);

        return view('deposits.agent_history', compact('deposits', 'activeCart'));
    }

    /**
     * Owner & Admin: Rekapitulasi Kas & Pengesahan Setoran Seluruh Agen
     */
    public function ownerIndex(Request $request)
    {
        $query = AgentDeposit::with(['agent', 'approver'])->withCount('payments');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Sesuai arahan Owner: Jika agen belum ajukan maka ke owner belum masuk data apa-apa.
            // Jangan tampilkan keranjang berjalan 'pending' secara default.
            $query->where('status', '!=', 'pending');
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('deposit_number', 'like', "%{$s}%")
                  ->orWhereHas('agent', function ($aq) use ($s) {
                      $aq->where('name', 'like', "%{$s}%")
                         ->orWhere('username', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        // Pilihan jumlah baris: 15, 25, 50, 100, atau 'all' (Tampilkan Semuanya)
        $perPage = $request->input('per_page', '15');
        if ($perPage === 'all') {
            $totalCount = (clone $query)->count();
            $deposits = $query->latest()->paginate(max(1, $totalCount))->withQueryString();
        } else {
            $perPageInt = in_array((int)$perPage, [15, 25, 50, 100]) ? (int)$perPage : 15;
            $deposits = $query->latest()->paginate($perPageInt)->withQueryString();
        }

        $agents = User::where('role', 'agent')->orderBy('name')->get();

        $submittedCount = AgentDeposit::where('status', 'submitted')->count();
        $totalApprovedAmount = AgentDeposit::where('status', 'approved')->sum('total_amount');

        return view('deposits.owner_index', compact('deposits', 'agents', 'submittedCount', 'totalApprovedAmount', 'perPage'));
    }

    /**
     * Admin: Sahkan / Validasi Setoran Masal (Khusus Akun Admin)
     */
    public function bulkApprove(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Aksi masal pengesahan setoran hanya diizinkan untuk akun Admin.');
        }

        $request->validate([
            'deposit_ids' => 'required|array|min:1',
            'deposit_ids.*' => 'required|integer|exists:agent_deposits,id',
            'notes' => 'nullable|string|max:255',
        ]);

        $depositIds = $request->input('deposit_ids', []);
        $deposits = AgentDeposit::with('agent')->whereIn('id', $depositIds)
            ->where('status', '!=', 'approved')
            ->get();

        if ($deposits->isEmpty()) {
            return back()->with('error', 'Tidak ada berkas setoran terpilih yang dapat disahkan (mungkin sudah disahkan sebelumnya).');
        }

        $approvedCount = 0;
        $totalAmount = 0;

        foreach ($deposits as $deposit) {
            $deposit->approve($user->id, $request->input('notes'));
            $approvedCount++;
            $totalAmount += (float)$deposit->total_amount;
        }

        return redirect()->route('owner.deposits.index', $request->only(['status', 'agent_id', 'per_page', 'search']))
            ->with('success', "Berhasil mengesahkan {$approvedCount} berkas setoran agen sekaligus dengan total kas Rp " . number_format($totalAmount, 0, ',', '.') . '. Seluruh ceklis terkait telah terkunci sah.');
    }

    /**
     * Admin: Tolak Setoran Masal (Khusus Akun Admin)
     */
    public function bulkReject(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Aksi masal penolakan setoran hanya diizinkan untuk akun Admin.');
        }

        $request->validate([
            'deposit_ids' => 'required|array|min:1',
            'deposit_ids.*' => 'required|integer|exists:agent_deposits,id',
            'notes' => 'required|string|max:255',
        ]);

        $depositIds = $request->input('deposit_ids', []);
        $deposits = AgentDeposit::with('agent')->whereIn('id', $depositIds)
            ->where('status', '!=', 'approved')
            ->get();

        if ($deposits->isEmpty()) {
            return back()->with('error', 'Tidak ada berkas setoran terpilih yang dapat ditolak (mungkin sudah disahkan).');
        }

        $rejectedCount = 0;
        $totalAmount = 0;

        foreach ($deposits as $deposit) {
            $deposit->reject($user->id, $request->input('notes'));
            $rejectedCount++;
            $totalAmount += (float)$deposit->total_amount;
        }

        return redirect()->route('owner.deposits.index', $request->only(['status', 'agent_id', 'per_page', 'search']))
            ->with('success', "Berhasil menolak {$rejectedCount} berkas setoran agen dengan total Rp " . number_format($totalAmount, 0, ',', '.') . ". Seluruh berkas telah dikembalikan untuk direvisi oleh agen.");
    }

    /**
     * Owner: Detail Lembar Rincian Setoran Agen
     */
    public function ownerShow(AgentDeposit $deposit)
    {
        $deposit->load([
            'agent',
            'approver',
            'payments.customerPackage.customer',
            'payments.customerPackage.package',
        ]);

        return view('deposits.owner_show', compact('deposit'));
    }

    /**
     * Owner: Sahkan / Validasi Setoran
     */
    public function ownerApprove(Request $request, AgentDeposit $deposit)
    {
        $owner = Auth::user();

        if ($deposit->status === 'approved') {
            return back()->with('error', 'Setoran ini sudah disahkan sebelumnya.');
        }

        $deposit->approve($owner->id, $request->input('notes'));

        return redirect()->route('owner.deposits.index')
            ->with('success', 'Setoran ' . $deposit->deposit_number . ' milik ' . $deposit->agent->name . ' sebesar ' . $deposit->formatted_total . ' BERHASIL DISAHKAN. Seluruh ceklis terkunci permanen.');
    }

    /**
     * Owner: Tolak Setoran (Minta Revisi Kas Fisik)
     */
    public function ownerReject(Request $request, AgentDeposit $deposit)
    {
        $request->validate([
            'notes' => 'required|string|max:255',
        ]);

        $owner = Auth::user();
        $deposit->reject($owner->id, $request->notes);

        return redirect()->route('owner.deposits.index')
            ->with('success', 'Setoran telah ditolak dengan catatan: ' . $request->notes);
    }

    /**
     * Agen: Buka kembali berkas setoran yang ditolak untuk diperbaiki / disesuaikan
     */
    public function reopenRejected(AgentDeposit $deposit)
    {
        $user = Auth::user();
        if ($deposit->agent_id !== $user->id && !$user->isOwner()) {
            abort(403);
        }

        if ($deposit->status !== 'rejected') {
            return back()->with('error', 'Hanya setoran yang ditolak yang dapat dibuka kembali.');
        }

        // Cari keranjang aktif saat ini (jika ada)
        $currentPending = AgentDeposit::where('agent_id', $deposit->agent_id)
            ->where('status', 'pending')
            ->where('id', '!=', $deposit->id)
            ->first();

        if ($currentPending && $currentPending->payments()->count() > 0) {
            // Gabungkan pembayaran dari setoran yang ditolak ke keranjang aktif
            \App\Models\Payment::where('agent_deposit_id', $deposit->id)->update([
                'agent_deposit_id' => $currentPending->id,
            ]);
            $currentPending->recalculateTotal();
            $deposit->delete();
        } else {
            // Hapus keranjang pending kosong jika ada, lalu jadikan berkas ini sebagai pending kembali
            if ($currentPending) {
                $currentPending->delete();
            }
            $deposit->update([
                'status' => 'pending',
                'submitted_at' => null,
                'approved_at' => null,
                'approved_by' => null,
            ]);
            $deposit->recalculateTotal();
        }

        return redirect()->route('agent.cart')
            ->with('success', 'Setoran ' . $deposit->deposit_number . ' telah dikembalikan ke Keranjang Aktif untuk direvisi dan diceklis ulang.');
    }
}

