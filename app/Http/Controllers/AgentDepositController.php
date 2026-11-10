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
     * Owner: Rekapitulasi Kas & Pengesahan Setoran Seluruh Agen
     */
    public function ownerIndex(Request $request)
    {
        $query = AgentDeposit::with(['agent', 'approver'])->withCount('payments');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        $deposits = $query->latest()->paginate(15)->withQueryString();
        $agents = User::where('role', 'agent')->get();

        $submittedCount = AgentDeposit::where('status', 'submitted')->count();
        $totalApprovedAmount = AgentDeposit::where('status', 'approved')->sum('total_amount');

        return view('deposits.owner_index', compact('deposits', 'agents', 'submittedCount', 'totalApprovedAmount'));
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
}

