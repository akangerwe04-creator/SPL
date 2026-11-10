<?php

namespace App\Services;

use App\Models\Item;
use App\Models\CustomerPackage;
use App\Models\Packaging;
use App\Models\Package;

class WholesalePlannerService
{
    /**
     * Hitung kebutuhan belanja grosir barang pokok, bonus paket, kemasan wadah, & penyiapan uang tunai dari seluruh pesanan aktif
     * Serta rekapan jumlah masing-masing paket yang harus disiapkan untuk memenuhi permintaan konsumen.
     */
    public function calculateRequirements(): array
    {
        // Ambil semua master paket aktif untuk inisialisasi rekapan paket
        $allMasterPackages = Package::with(['packageItems.item'])->where('is_active', true)->get();

        $packageSummaryMap = [];
        foreach ($allMasterPackages as $p) {
            $packageSummaryMap[$p->id] = [
                'package_id' => $p->id,
                'package_code' => $p->code,
                'package_name' => $p->name,
                'package_type' => $p->type,
                'package_type_label' => match($p->type) {
                    'parsel' => 'Parsel Sembako / Lebaran',
                    'tabungan' => 'Tabungan SPL',
                    'ecer' => 'Paket Eceran / Bebas',
                    default => ucfirst($p->type),
                },
                'period_type' => $p->period_type,
                'installment_label' => $p->installment_label ?? ($p->period_type === 'harian' ? 'Harian' : 'Mingguan'),
                'installment_count' => (int) $p->installment_count,
                'installment_amount' => (float) $p->installment_amount,
                'target_nominal' => (float) ($p->target_nominal ?: $p->base_price),
                'base_price' => (float) $p->base_price,
                'description' => $p->description,
                'image_url' => $p->image_url,
                'total_orders' => 0,
                'pending_orders' => 0,
                'fulfilled_orders' => 0,
                'total_target_amount' => 0,
                'total_paid_amount' => 0,
                'total_cash_needed' => 0,
                'items_per_package' => [],
                'total_goods_needed' => [],
                'bonuses_needed' => [],
                'packagings_needed' => [],
                'agents' => [],
                'customers' => [],
            ];

            // Rincian komposisi standar per 1 paket
            if ($p->isParsel() || $p->isTabungan()) {
                foreach ($p->packageItems as $pi) {
                    if (!$pi->item) continue;
                    $packageSummaryMap[$p->id]['items_per_package'][] = [
                        'item_id' => $pi->item_id,
                        'item_name' => $pi->item->name,
                        'item_code' => $pi->item->code,
                        'category' => $pi->item->category,
                        'quantity' => $pi->quantity,
                        'unit' => $pi->unit ?: $pi->item->base_unit,
                        'is_bonus' => (bool) $pi->is_bonus,
                        'is_uang' => $pi->item->isUang(),
                        'unit_price' => (float) $pi->item->unit_price,
                    ];
                }
            }
        }

        // Ambil semua customer package yang aktif / belum dibatalkan
        $activePackages = CustomerPackage::with([
            'customer',
            'agent',
            'packaging',
            'package.packageItems.item',
            'customItems.item',
            'partialFulfillment.items.item',
        ])->where('status', '!=', 'canceled')->get();

        $regularTotals = [];   // [item_id => ['qty' => 0, 'from_parsel' => 0, 'from_ecer' => 0]]
        $bonusTotals = [];     // [item_id => ['qty' => 0, 'packages' => []]]
        $packagingTotals = []; // [packaging_id => ['packaging' => ..., 'total_qty' => 0, ...]]
        $cashRequirements = []; // Rincian uang tunai per konsumen & agen

        foreach ($activePackages as $cp) {
            $pkg = $cp->package;
            if (!$pkg) continue;

            $pkgId = $pkg->id;
            if (!isset($packageSummaryMap[$pkgId])) {
                $packageSummaryMap[$pkgId] = [
                    'package_id' => $pkg->id,
                    'package_code' => $pkg->code,
                    'package_name' => $pkg->name,
                    'package_type' => $pkg->type,
                    'package_type_label' => match($pkg->type) {
                        'parsel' => 'Parsel Sembako / Lebaran',
                        'tabungan' => 'Tabungan SPL',
                        'ecer' => 'Paket Eceran / Bebas',
                        default => ucfirst($pkg->type),
                    },
                    'period_type' => $pkg->period_type,
                    'installment_label' => $pkg->installment_label ?? ($pkg->period_type === 'harian' ? 'Harian' : 'Mingguan'),
                    'installment_count' => (int) $pkg->installment_count,
                    'installment_amount' => (float) $pkg->installment_amount,
                    'target_nominal' => (float) ($pkg->target_nominal ?: $pkg->base_price),
                    'base_price' => (float) $pkg->base_price,
                    'description' => $pkg->description,
                    'image_url' => $pkg->image_url,
                    'total_orders' => 0,
                    'pending_orders' => 0,
                    'fulfilled_orders' => 0,
                    'total_target_amount' => 0,
                    'total_paid_amount' => 0,
                    'total_cash_needed' => 0,
                    'items_per_package' => [],
                    'total_goods_needed' => [],
                    'bonuses_needed' => [],
                    'packagings_needed' => [],
                    'agents' => [],
                    'customers' => [],
                ];
            }

            // Update statistik order paket
            $packageSummaryMap[$pkgId]['total_orders'] += 1;
            if ($cp->fulfillment_status === 'fulfilled') {
                $packageSummaryMap[$pkgId]['fulfilled_orders'] += 1;
            } else {
                $packageSummaryMap[$pkgId]['pending_orders'] += 1;
            }
            $packageSummaryMap[$pkgId]['total_target_amount'] += (float) ($cp->total_target ?: $pkg->target_nominal);
            $packageSummaryMap[$pkgId]['total_paid_amount'] += (float) $cp->total_paid;

            $custName = $cp->customer?->name ?? 'Konsumen Umum';
            $custCode = $cp->customer?->code ?? '-';
            $custPhone = $cp->customer?->phone ?? '-';
            $custAddress = $cp->customer?->address ?? '-';
            $agentName = $cp->agent?->name ?? 'Tanpa Agen';
            $agentId = $cp->agent_id ?: 0;
            $agentPhone = $cp->agent?->phone ?? '-';
            $periodText = ($pkg->installment_label ?? 'Mingguan') . ' (' . $pkg->installment_count . ' ' . ($pkg->period_label ?? 'Minggu') . ')';

            // Catat agen pemesan paket
            if (!isset($packageSummaryMap[$pkgId]['agents'][$agentId])) {
                $packageSummaryMap[$pkgId]['agents'][$agentId] = [
                    'agent_id' => $agentId,
                    'name' => $agentName,
                    'phone' => $agentPhone,
                    'count' => 0,
                ];
            }
            $packageSummaryMap[$pkgId]['agents'][$agentId]['count'] += 1;

            // Catat data konsumen pemesan paket
            $packageSummaryMap[$pkgId]['customers'][] = [
                'customer_id' => $cp->customer_id,
                'customer_name' => $custName,
                'customer_code' => $custCode,
                'customer_phone' => $custPhone,
                'agent_name' => $agentName,
                'fulfillment_status' => $cp->fulfillment_status,
                'packaging_name' => $cp->packaging?->name ?? 'Standar',
                'total_target' => (float) ($cp->total_target ?: $pkg->target_nominal),
                'total_paid' => (float) $cp->total_paid,
                'progress_percent' => $cp->progress_percent,
                'is_bonus_forfeited' => (bool) $cp->is_bonus_forfeited,
            ];

            // 1. REKAP KEMASAN / WADAH (Koper, Keranjang, Box Hampers)
            if ($cp->packaging_id && $cp->packaging) {
                $kms = $cp->packaging;
                $kmsId = $kms->id;
                if (!isset($packagingTotals[$kmsId])) {
                    $packagingTotals[$kmsId] = [
                        'packaging_id' => $kmsId,
                        'code' => $kms->code,
                        'name' => $kms->name,
                        'cost_price' => (float) $kms->cost_price,
                        'price' => (float) ($cp->packaging_price ?: $kms->price),
                        'image_url' => $kms->image_url,
                        'image_path' => $kms->image_path,
                        'description' => $kms->description,
                        'total_qty' => 0,
                        'total_cost' => 0,
                        'recipients' => [],
                    ];
                }
                $packagingTotals[$kmsId]['total_qty'] += 1;
                $packagingTotals[$kmsId]['total_cost'] += (float) $kms->cost_price;
                $packagingTotals[$kmsId]['recipients'][] = [
                    'customer_name' => $custName,
                    'customer_code' => $custCode,
                    'agent_name' => $agentName,
                    'package_name' => $pkg->name,
                    'fulfillment_status' => $cp->fulfillment_status,
                ];

                // Kemasan pada ringkasan paket ini
                if (!isset($packageSummaryMap[$pkgId]['packagings_needed'][$kmsId])) {
                    $packageSummaryMap[$pkgId]['packagings_needed'][$kmsId] = [
                        'id' => $kms->id,
                        'code' => $kms->code,
                        'name' => $kms->name,
                        'cost_price' => (float) $kms->cost_price,
                        'qty' => 0,
                    ];
                }
                $packageSummaryMap[$pkgId]['packagings_needed'][$kmsId]['qty'] += 1;
            }

            // 2. REKAP ISI BARANG & UANG
            // Integrasi Tutup Buku: Jika ada keputusan pemenuhan khusus (PartialFulfillment / Keputusan Owner)
            if ($cp->partialFulfillment && in_array($cp->partialFulfillment->settlement_type, ['goods', 'custom', 'partial', 'cash'])) {
                $pf = $cp->partialFulfillment;

                // Rekap Uang Tunai dari Keputusan Owner (jika ada nominal pencairan uang)
                if ((float) $pf->cash_payout > 0) {
                    $cashPayout = (float) $pf->cash_payout;
                    $cashRequirements[] = [
                        'customer_id' => $cp->customer_id,
                        'customer_name' => $custName,
                        'customer_code' => $custCode,
                        'customer_phone' => $custPhone,
                        'customer_address' => $custAddress,
                        'agent_id' => $cp->agent_id,
                        'agent_name' => $agentName,
                        'agent_phone' => $agentPhone,
                        'package_id' => $pkg->id,
                        'package_name' => $pkg->name,
                        'package_code' => $pkg->code,
                        'package_type' => $pkg->type,
                        'period_text' => $periodText,
                        'cash_description' => 'Pencairan Dana Tutup Buku (Keputusan Owner)',
                        'cash_amount' => $cashPayout,
                        'total_paid' => (float) $cp->total_paid,
                        'progress_percent' => $cp->progress_percent,
                        'is_bonus_forfeited' => (bool) $cp->is_bonus_forfeited,
                        'forfeit_reason' => $cp->forfeit_reason,
                        'fulfillment_status' => $cp->fulfillment_status,
                    ];
                    $packageSummaryMap[$pkgId]['total_cash_needed'] += $cashPayout;
                }

                // Rekap Barang Hasil Keputusan Owner (Barang Pokok Pengganti & Hadiah Bonus)
                foreach ($pf->items as $pfi) {
                    if (!$pfi->item_id || !$pfi->item) continue;

                    if ($pfi->item->isUang()) {
                        $cashAmount = (float) ($pfi->unit_price * $pfi->quantity);
                        $cashRequirements[] = [
                            'customer_id' => $cp->customer_id,
                            'customer_name' => $custName,
                            'customer_code' => $custCode,
                            'customer_phone' => $custPhone,
                            'customer_address' => $custAddress,
                            'agent_id' => $cp->agent_id,
                            'agent_name' => $agentName,
                            'agent_phone' => $agentPhone,
                            'package_id' => $pkg->id,
                            'package_name' => $pkg->name,
                            'package_code' => $pkg->code,
                            'package_type' => $pkg->type,
                            'period_text' => $periodText,
                            'cash_description' => $pfi->item->name . ($pfi->quantity > 1 ? ' (' . $pfi->quantity . 'x)' : ''),
                            'cash_amount' => $cashAmount,
                            'total_paid' => (float) $cp->total_paid,
                            'progress_percent' => $cp->progress_percent,
                            'is_bonus_forfeited' => false,
                            'forfeit_reason' => null,
                            'fulfillment_status' => $cp->fulfillment_status,
                        ];
                        $packageSummaryMap[$pkgId]['total_cash_needed'] += $cashAmount;
                    } elseif ($pfi->is_bonus) {
                        $this->accumulateBonus($bonusTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Bonus Tutup Buku)');

                        $bId = $pfi->item_id;
                        if (!isset($packageSummaryMap[$pkgId]['bonuses_needed'][$bId])) {
                            $packageSummaryMap[$pkgId]['bonuses_needed'][$bId] = [
                                'item_id' => $bId,
                                'item_name' => $pfi->item->name,
                                'item_code' => $pfi->item->code,
                                'category' => $pfi->item->category ?: 'Hadiah Bonus',
                                'qty_per_unit' => $pfi->quantity,
                                'unit' => $pfi->item->base_unit,
                                'total_qty' => 0,
                            ];
                        }
                        $packageSummaryMap[$pkgId]['bonuses_needed'][$bId]['total_qty'] += $pfi->quantity;
                    } else {
                        $this->accumulateRegular($regularTotals, $pfi->item_id, $pfi->quantity, 'from_parsel');

                        $gId = $pfi->item_id;
                        if (!isset($packageSummaryMap[$pkgId]['total_goods_needed'][$gId])) {
                            $packageSummaryMap[$pkgId]['total_goods_needed'][$gId] = [
                                'item_id' => $gId,
                                'item_name' => $pfi->item->name,
                                'item_code' => $pfi->item->code,
                                'category' => $pfi->item->category ?: 'Sembako',
                                'qty_per_unit' => $pfi->quantity,
                                'unit' => $pfi->item->base_unit,
                                'wholesale_unit' => $pfi->item->wholesale_unit,
                                'conversion_rate' => $pfi->item->conversion_rate,
                                'total_qty' => 0,
                            ];
                        }
                        $packageSummaryMap[$pkgId]['total_goods_needed'][$gId]['total_qty'] += $pfi->quantity;
                    }
                }

            } elseif ($pkg->isParsel()) {
                // Parsel regular items & bonus items
                foreach ($pkg->packageItems as $pi) {
                    if (!$pi->item_id || !$pi->item) continue;

                    if ($pi->item->isUang()) {
                        // Item berkategori Uang di dalam Parsel
                        $cashAmount = (float) ($pi->item->unit_price * $pi->quantity);
                        $cashRequirements[] = [
                            'customer_id' => $cp->customer_id,
                            'customer_name' => $custName,
                            'customer_code' => $custCode,
                            'customer_phone' => $custPhone,
                            'customer_address' => $custAddress,
                            'agent_id' => $cp->agent_id,
                            'agent_name' => $agentName,
                            'agent_phone' => $agentPhone,
                            'package_id' => $pkg->id,
                            'package_name' => $pkg->name,
                            'package_code' => $pkg->code,
                            'package_type' => 'parsel',
                            'period_text' => $periodText,
                            'cash_description' => $pi->item->name . ($pi->quantity > 1 ? ' (' . $pi->quantity . 'x)' : ''),
                            'cash_amount' => $cashAmount,
                            'total_paid' => (float) $cp->total_paid,
                            'progress_percent' => $cp->progress_percent,
                            'is_bonus_forfeited' => false,
                            'forfeit_reason' => null,
                            'fulfillment_status' => $cp->fulfillment_status,
                        ];

                        $packageSummaryMap[$pkgId]['total_cash_needed'] += $cashAmount;
                    } else {
                        if ($pi->is_bonus) {
                            $this->accumulateBonus($bonusTotals, $pi->item_id, $pi->quantity, $pkg->name);

                            $bId = $pi->item_id;
                            if (!isset($packageSummaryMap[$pkgId]['bonuses_needed'][$bId])) {
                                $packageSummaryMap[$pkgId]['bonuses_needed'][$bId] = [
                                    'item_id' => $bId,
                                    'item_name' => $pi->item->name,
                                    'item_code' => $pi->item->code,
                                    'category' => $pi->item->category ?: 'Hadiah Bonus',
                                    'qty_per_unit' => $pi->quantity,
                                    'unit' => $pi->unit ?: $pi->item->base_unit,
                                    'total_qty' => 0,
                                ];
                            }
                            $packageSummaryMap[$pkgId]['bonuses_needed'][$bId]['total_qty'] += $pi->quantity;
                        } else {
                            $this->accumulateRegular($regularTotals, $pi->item_id, $pi->quantity, 'from_parsel');

                            $gId = $pi->item_id;
                            if (!isset($packageSummaryMap[$pkgId]['total_goods_needed'][$gId])) {
                                $packageSummaryMap[$pkgId]['total_goods_needed'][$gId] = [
                                    'item_id' => $gId,
                                    'item_name' => $pi->item->name,
                                    'item_code' => $pi->item->code,
                                    'category' => $pi->item->category ?: 'Sembako',
                                    'qty_per_unit' => $pi->quantity,
                                    'unit' => $pi->unit ?: $pi->item->base_unit,
                                    'wholesale_unit' => $pi->item->wholesale_unit,
                                    'conversion_rate' => $pi->item->conversion_rate,
                                    'total_qty' => 0,
                                ];
                            }
                            $packageSummaryMap[$pkgId]['total_goods_needed'][$gId]['total_qty'] += $pi->quantity;
                        }
                    }
                }
            } elseif ($pkg->isTabungan()) {
                // Tabungan: Payout utama adalah Uang Tunai kepada Nasabah!
                $uangItem = $pkg->packageItems->first(fn($pi) => $pi->item && $pi->item->isUang() && !$pi->is_bonus);
                $tabunganCash = $uangItem ? (float) ($uangItem->item->unit_price * $uangItem->quantity) : (float) ($pkg->base_price ?: ($cp->total_target ?: $pkg->target_nominal));

                $cashRequirements[] = [
                    'customer_id' => $cp->customer_id,
                    'customer_name' => $custName,
                    'customer_code' => $custCode,
                    'customer_phone' => $custPhone,
                    'customer_address' => $custAddress,
                    'agent_id' => $cp->agent_id,
                    'agent_name' => $agentName,
                    'agent_phone' => $agentPhone,
                    'package_id' => $pkg->id,
                    'package_name' => $pkg->name,
                    'package_code' => $pkg->code,
                    'package_type' => 'tabungan',
                    'period_text' => $periodText,
                    'cash_description' => $uangItem ? $uangItem->item->name : 'Pencairan Tabungan Lebaran (' . $pkg->name . ')',
                    'cash_amount' => $tabunganCash,
                    'total_paid' => (float) $cp->total_paid,
                    'progress_percent' => $cp->progress_percent,
                    'is_bonus_forfeited' => (bool) $cp->is_bonus_forfeited,
                    'forfeit_reason' => $cp->forfeit_reason,
                    'fulfillment_status' => $cp->fulfillment_status,
                ];

                $packageSummaryMap[$pkgId]['total_cash_needed'] += $tabunganCash;

                // Tabungan: Barang fisik bonus dimasukkan jika bonus belum hangus!
                if (!$cp->is_bonus_forfeited) {
                    foreach ($pkg->packageItems as $pi) {
                        if ($pi->is_bonus && $pi->item_id && $pi->item) {
                            if (!$pi->item->isUang()) {
                                $this->accumulateBonus($bonusTotals, $pi->item_id, $pi->quantity, $pkg->name);

                                $bId = $pi->item_id;
                                if (!isset($packageSummaryMap[$pkgId]['bonuses_needed'][$bId])) {
                                    $packageSummaryMap[$pkgId]['bonuses_needed'][$bId] = [
                                        'item_id' => $bId,
                                        'item_name' => $pi->item->name,
                                        'item_code' => $pi->item->code,
                                        'category' => $pi->item->category ?: 'Hadiah Bonus',
                                        'qty_per_unit' => $pi->quantity,
                                        'unit' => $pi->unit ?: $pi->item->base_unit,
                                        'total_qty' => 0,
                                    ];
                                }
                                $packageSummaryMap[$pkgId]['bonuses_needed'][$bId]['total_qty'] += $pi->quantity;
                            }
                        }
                    }
                }
            } elseif ($pkg->isEcer()) {
                // Paket Ecer Dinamis rakitan
                foreach ($cp->customItems as $ci) {
                    if (!$ci->item_id || !$ci->item) continue;

                    if ($ci->item->isUang()) {
                        $cashAmount = (float) ($ci->unit_price * $ci->quantity);
                        $cashRequirements[] = [
                            'customer_id' => $cp->customer_id,
                            'customer_name' => $custName,
                            'customer_code' => $custCode,
                            'customer_phone' => $custPhone,
                            'customer_address' => $custAddress,
                            'agent_id' => $cp->agent_id,
                            'agent_name' => $agentName,
                            'agent_phone' => $agentPhone,
                            'package_id' => $pkg->id,
                            'package_name' => $pkg->name,
                            'package_code' => $pkg->code,
                            'package_type' => 'ecer',
                            'period_text' => $periodText,
                            'cash_description' => $ci->item->name . ($ci->quantity > 1 ? ' (' . $ci->quantity . 'x)' : ''),
                            'cash_amount' => $cashAmount,
                            'total_paid' => (float) $cp->total_paid,
                            'progress_percent' => $cp->progress_percent,
                            'is_bonus_forfeited' => false,
                            'forfeit_reason' => null,
                            'fulfillment_status' => $cp->fulfillment_status,
                        ];

                        $packageSummaryMap[$pkgId]['total_cash_needed'] += $cashAmount;
                    } else {
                        $this->accumulateRegular($regularTotals, $ci->item_id, $ci->quantity, 'from_ecer');

                        $gId = $ci->item_id;
                        if (!isset($packageSummaryMap[$pkgId]['total_goods_needed'][$gId])) {
                            $packageSummaryMap[$pkgId]['total_goods_needed'][$gId] = [
                                'item_id' => $gId,
                                'item_name' => $ci->item->name,
                                'item_code' => $ci->item->code,
                                'category' => $ci->item->category ?: 'Eceran',
                                'qty_per_unit' => null,
                                'unit' => $ci->unit ?: $ci->item->base_unit,
                                'wholesale_unit' => $ci->item->wholesale_unit,
                                'conversion_rate' => $ci->item->conversion_rate,
                                'total_qty' => 0,
                            ];
                        }
                        $packageSummaryMap[$pkgId]['total_goods_needed'][$gId]['total_qty'] += $ci->quantity;
                    }
                }
            }
        }

        // 3. OLAH TABEL 1: BARANG POKOK FISIK (PARSEL & ECERAN)
        $allItemIds = array_unique(array_merge(array_keys($regularTotals), array_keys($bonusTotals)));
        $itemsMaster = Item::whereIn('id', $allItemIds)->get()->keyBy('id');

        $goodsReport = [];
        $totalEstimatedCost = 0;
        $totalItemsCount = 0;
        $categoryBreakdown = [];

        foreach ($regularTotals as $itemId => $data) {
            /** @var Item $item */
            $item = $itemsMaster->get($itemId);
            if (!$item) continue;

            $totalBase = $data['qty'];
            $rate = $item->conversion_rate > 0 ? (float)$item->conversion_rate : 1;
            $wholesaleFull = floor($totalBase / $rate);
            $wholesaleRemainder = $totalBase - ($wholesaleFull * $rate);
            $wholesaleCeil = (int) ceil($totalBase / $rate);
            
            $costPerUnit = $item->cost_price > 0 ? (float)$item->cost_price : ((float)$item->unit_price * 0.85);
            $estCost = $totalBase * $costPerUnit;
            $totalEstimatedCost += $estCost;
            $totalItemsCount += $totalBase;

            $cat = $item->category ?: 'Lain-lain';
            if (!isset($categoryBreakdown[$cat])) {
                $categoryBreakdown[$cat] = [
                    'category' => $cat,
                    'total_base_qty' => 0,
                    'estimated_cost' => 0,
                ];
            }
            $categoryBreakdown[$cat]['total_base_qty'] += $totalBase;
            $categoryBreakdown[$cat]['estimated_cost'] += $estCost;

            $goodsReport[] = [
                'item_id' => $item->id,
                'item_code' => $item->code,
                'item_name' => $item->name,
                'category' => $cat,
                'base_unit' => $item->base_unit,
                'wholesale_unit' => $item->wholesale_unit,
                'conversion_rate' => $rate,
                'total_base_qty' => $totalBase,
                'from_parsel' => $data['from_parsel'],
                'from_ecer' => $data['from_ecer'],
                'wholesale_full' => (int) $wholesaleFull,
                'wholesale_remainder' => (int) $wholesaleRemainder,
                'wholesale_ceil' => $wholesaleCeil,
                'wholesale_text' => $wholesaleFull . ' ' . $item->wholesale_unit . ($wholesaleRemainder > 0 ? ' + ' . $wholesaleRemainder . ' ' . $item->base_unit : ''),
                'cost_price' => $costPerUnit,
                'estimated_cost' => $estCost,
            ];
        }

        // 4. OLAH TABEL 2: BARANG BONUS PAKET (TABEL TERPISAH SESUAI ARAHAN OWNER)
        $bonusReport = [];
        $totalBonusCount = 0;
        $totalBonusCost = 0;

        foreach ($bonusTotals as $itemId => $data) {
            /** @var Item $item */
            $item = $itemsMaster->get($itemId);
            if (!$item) continue;

            $totalBase = $data['qty'];
            $rate = $item->conversion_rate > 0 ? (float)$item->conversion_rate : 1;
            $wholesaleFull = floor($totalBase / $rate);
            $wholesaleRemainder = $totalBase - ($wholesaleFull * $rate);
            $wholesaleCeil = (int) ceil($totalBase / $rate);
            
            $costPerUnit = $item->cost_price > 0 ? (float)$item->cost_price : ((float)$item->unit_price * 0.85);
            $estCost = $totalBase * $costPerUnit;
            $totalBonusCost += $estCost;
            $totalBonusCount += $totalBase;

            $bonusReport[] = [
                'item_id' => $item->id,
                'item_code' => $item->code,
                'item_name' => $item->name,
                'category' => $item->category ?: 'Bonus Hadiah',
                'base_unit' => $item->base_unit,
                'wholesale_unit' => $item->wholesale_unit,
                'conversion_rate' => $rate,
                'total_base_qty' => $totalBase,
                'packages_list' => array_unique($data['packages']),
                'wholesale_full' => (int) $wholesaleFull,
                'wholesale_remainder' => (int) $wholesaleRemainder,
                'wholesale_ceil' => $wholesaleCeil,
                'wholesale_text' => $wholesaleFull . ' ' . $item->wholesale_unit . ($wholesaleRemainder > 0 ? ' + ' . $wholesaleRemainder . ' ' . $item->base_unit : ''),
                'cost_price' => $costPerUnit,
                'estimated_cost' => $estCost,
            ];
        }

        // 5. OLAH TABEL 3: KEMASAN / WADAH PARSEL (TABEL TERPISAH DENGAN FOTO)
        $totalPackagingUnits = 0;
        $totalPackagingCost = 0;
        foreach ($packagingTotals as $kms) {
            $totalPackagingUnits += $kms['total_qty'];
            $totalPackagingCost += $kms['total_cost'];
        }

        // 6. OLAH TABEL 4: PENYIAPAN UANG TUNAI
        $cashByAgent = [];
        $totalCashAmount = 0;

        foreach ($cashRequirements as $cr) {
            $agName = $cr['agent_name'];
            if (!isset($cashByAgent[$agName])) {
                $cashByAgent[$agName] = [
                    'agent_id' => $cr['agent_id'],
                    'agent_name' => $agName,
                    'agent_phone' => $cr['agent_phone'],
                    'total_cash' => 0,
                    'recipient_count' => 0,
                    'recipients' => [],
                ];
            }
            $cashByAgent[$agName]['total_cash'] += $cr['cash_amount'];
            $cashByAgent[$agName]['recipient_count'] += 1;
            $cashByAgent[$agName]['recipients'][] = $cr;
            $totalCashAmount += $cr['cash_amount'];
        }

        // 7. OLAH TABEL 0: REKAPAN JUMLAH PAKET YANG HARUS DISIAPKAN (PER JENIS PAKET)
        $totalPackagesCount = 0;
        $totalPackagesPending = 0;
        $totalPackagesFulfilled = 0;
        $totalParselCount = 0;
        $totalTabunganCount = 0;
        $totalEcerCount = 0;
        $packagesWithOrdersCount = 0;

        foreach ($packageSummaryMap as &$pkgSummary) {
            $pkgSummary['total_goods_needed'] = array_values($pkgSummary['total_goods_needed']);
            $pkgSummary['bonuses_needed'] = array_values($pkgSummary['bonuses_needed']);
            $pkgSummary['packagings_needed'] = array_values($pkgSummary['packagings_needed']);
            $pkgSummary['agents'] = array_values($pkgSummary['agents']);

            if ($pkgSummary['total_orders'] > 0) {
                $packagesWithOrdersCount++;
                $totalPackagesCount += $pkgSummary['total_orders'];
                $totalPackagesPending += $pkgSummary['pending_orders'];
                $totalPackagesFulfilled += $pkgSummary['fulfilled_orders'];

                if ($pkgSummary['package_type'] === 'parsel') {
                    $totalParselCount += $pkgSummary['total_orders'];
                } elseif ($pkgSummary['package_type'] === 'tabungan') {
                    $totalTabunganCount += $pkgSummary['total_orders'];
                } elseif ($pkgSummary['package_type'] === 'ecer') {
                    $totalEcerCount += $pkgSummary['total_orders'];
                }
            }
        }
        unset($pkgSummary);

        // Urutkan paket: yang memiliki pesanan terbanyak di atas, kemudian abjad nama paket
        uasort($packageSummaryMap, function ($a, $b) {
            if ($a['total_orders'] !== $b['total_orders']) {
                return $b['total_orders'] <=> $a['total_orders'];
            }
            return strcmp($a['package_name'], $b['package_name']);
        });

        $grandTotalFund = $totalCashAmount + $totalEstimatedCost + $totalBonusCost + $totalPackagingCost;

        return [
            'packages_to_prepare' => array_values($packageSummaryMap),
            'items' => $goodsReport,
            'bonus_items' => $bonusReport,
            'packaging_items' => array_values($packagingTotals),
            'cash_items' => $cashRequirements,
            'cash_requirements' => $cashRequirements,
            'cash_by_agent' => array_values($cashByAgent),
            'summary' => [
                'total_packages_count' => $totalPackagesCount,
                'total_packages_pending' => $totalPackagesPending,
                'total_packages_fulfilled' => $totalPackagesFulfilled,
                'total_parsel_count' => $totalParselCount,
                'total_tabungan_count' => $totalTabunganCount,
                'total_ecer_count' => $totalEcerCount,
                'packages_with_orders_count' => $packagesWithOrdersCount,
                'total_packages_master' => count($packageSummaryMap),
                'total_unique_items' => count($goodsReport),
                'total_base_pieces' => $totalItemsCount,
                'total_estimated_cost' => $totalEstimatedCost,
                'total_unique_bonus' => count($bonusReport),
                'total_bonus_pieces' => $totalBonusCount,
                'total_bonus_cost' => $totalBonusCost,
                'total_packaging_types' => count($packagingTotals),
                'total_packaging_units' => $totalPackagingUnits,
                'total_packaging_cost' => $totalPackagingCost,
                'total_cash_amount' => $totalCashAmount,
                'total_cash_recipients' => count($cashRequirements),
                'total_cash_agents' => count($cashByAgent),
                'grand_total_goods_fund' => $totalEstimatedCost + $totalBonusCost + $totalPackagingCost,
                'grand_total_fund' => $grandTotalFund,
                'active_packages_count' => $activePackages->count(),
                'categories' => array_values($categoryBreakdown),
            ],
        ];
    }

    private function accumulateRegular(array &$itemTotals, int $itemId, int $qty, string $sourceKey): void
    {
        if (!isset($itemTotals[$itemId])) {
            $itemTotals[$itemId] = [
                'qty' => 0,
                'from_parsel' => 0,
                'from_ecer' => 0,
            ];
        }

        $itemTotals[$itemId]['qty'] += $qty;
        $itemTotals[$itemId][$sourceKey] += $qty;
    }

    private function accumulateBonus(array &$bonusTotals, int $itemId, int $qty, string $packageName): void
    {
        if (!isset($bonusTotals[$itemId])) {
            $bonusTotals[$itemId] = [
                'qty' => 0,
                'packages' => [],
            ];
        }

        $bonusTotals[$itemId]['qty'] += $qty;
        $bonusTotals[$itemId]['packages'][] = $packageName;
    }
}
