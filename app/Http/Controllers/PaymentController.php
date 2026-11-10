<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Models\CustomerPackage;
use App\Models\AgentDeposit;

class PaymentController extends Controller
{
    /**
     * Tampilan Lembar Ceklis Tagihan (Smart Payment Grid)
     */
    public function checklist(CustomerPackage $customerPackage)
    {
        $user = Auth::user();

        if ($user->isAgent() && $customerPackage->agent_id !== $user->id) {
            abort(403, 'Anda hanya dapat menagih konsumen binaan Anda sendiri.');
        }

        $customerPackage->load([
            'customer',
            'package.packageItems.item',
            'payments.deposit',
            'customItems.item',
        ]);

        // Dapatkan keranjang aktif agen saat ini
        $activeCart = AgentDeposit::getOrCreateActiveCart($user->isAgent() ? $user->id : $customerPackage->agent_id);

        return view('payments.checklist', compact('customerPackage', 'activeCart'));
    }

    /**
     * Toggle Ceklis Pembayaran (AJAX / Realtime JSON support)
     */
    public function toggle(Request $request, Payment $payment)
    {
        $user = Auth::user();

        // Verifikasi agen yang berhak mencentang (atau agen pembina paket)
        $agentId = $user->isAgent() ? $user->id : ($payment->agent_id ?: $payment->customerPackage->agent_id);

        $result = $payment->toggleChecklist($agentId);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}

