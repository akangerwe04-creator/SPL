<?php

namespace App\Services;

use App\Models\Item;
use App\Models\CustomerPackage;
use App\Models\Packaging;
use App\Models\Package;
use App\Models\CatalogSetting;

class WholesalePlannerService
{
    /**
     * Hitung kebutuhan belanja grosir barang pokok, bonus paket, kemasan wadah, & penyiapan uang tunai dari seluruh pesanan aktif
     * Serta rekapan jumlah masing-masing paket yang harus disiapkan untuk memenuhi permintaan konsumen.
     * 
     * @param string $scope 'safe' = Hanya konsumen lolos titik aman / ACC owner pada tutup buku (Standar Belanja SPL), 'raw_all' = Seluruh pesanan konsumen aktif tanpa filter
     */
    public function calculateRequirements(string $scope = 'safe'): array
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

        // Ambil semua customer package yang relevan:
        // 1. Paket aktif (status != 'canceled')
        // 2. ATAU paket yang telah ditentukan barangnya oleh Owner (memiliki partialFulfillment dengan barang / dana, termasuk stop konsumen yang di-ACC)
        $allPackages = CustomerPackage::with([
            'customer',
            'agent',
            'packaging',
            'package.packageItems.item',
            'customItems.item',
            'partialFulfillment.items.item',
        ])->where(function ($q) {
            $q->where('status', '!=', 'canceled')
              ->orWhere(function ($sq) {
                  $sq->whereHas('partialFulfillment', function ($pfq) {
                      $pfq->where(function ($cq) {
                          $cq->whereHas('items')->orWhere('cash_payout', '>', 0);
                      });
                  });
              });
        })->get();

        $appSetting = CatalogSetting::current();
        $isBookClosed = (bool) ($appSetting->is_book_closed ?? false);

        $parselHarianTotals = [];   // [item_id => ['qty' => 0, 'packages' => []]]
        $parselMingguanTotals = []; // [item_id => ['qty' => 0, 'packages' => []]]
        $ecerTotals = [];           // [item_id => ['qty' => 0, 'packages' => []]]
        $tabunganBonusTotals = [];  // [item_id => ['qty' => 0, 'packages' => []]]
        $parselBonusTotals = [];    // [item_id => ['qty' => 0, 'packages' => []]]
        $allGoodsTotals = [];       // [item_id => ['qty' => 0, 'packages' => [], 'from_parsel_harian' => 0, 'from_parsel_mingguan' => 0, 'from_ecer' => 0, 'from_tabungan_bonus' => 0, 'from_parsel_bonus' => 0]]
        $packagingTotals = [];      // [packaging_id => ['packaging' => ..., 'total_qty' => 0, ...]]
        $cashRequirements = [];     // Rincian uang tunai per konsumen & agen
        $excludedIncompleteCustomers = []; // Konsumen tidak selesai yang belum ditentukan barangnya oleh owner
        $eligibleCustomersCount = 0;
        $safeThresholdReachedCount = 0;
        $pendingSafeCount = 0;

        foreach ($allPackages as $cp) {
            $pkg = $cp->package;
            if (!$pkg) continue;

            $pkgId = $pkg->id;

            $custName = $cp->customer?->name ?? 'Konsumen Umum';
            $custCode = $cp->customer?->code ?? '-';
            $custPhone = $cp->customer?->phone ?? '-';
            $custAddress = $cp->customer?->address ?? '-';
            $agentName = $cp->agent?->name ?? 'Tanpa Agen';
            $agentId = $cp->agent_id ?: 0;
            $agentPhone = $cp->agent?->phone ?? '-';
            $periodText = ($pkg->installment_label ?? 'Mingguan') . ' (' . $pkg->installment_count . ' ' . ($pkg->period_label ?? 'Minggu') . ')';

            $isSafeReached = $cp->isSafeThresholdReached();
            $hasOwnerDecision = (bool) ($cp->partialFulfillment && ($cp->partialFulfillment->items->isNotEmpty() || (float)$cp->partialFulfillment->cash_payout > 0));

            if ($isSafeReached || ($isBookClosed && $hasOwnerDecision)) {
                $safeThresholdReachedCount++;
            } else {
                $pendingSafeCount++;
            }

            // ATURAN RESMI OWNER SPL:
            // Sesuai instruksi Owner:
            // Yang statusnya belum mencapai titik aman barangnya TIDAK dimasukkan ke daftar belanja,
            // KECUALI jika statusnya sudah melewati titik aman,
            // DAN KECUALI Owner sudah melakukan rekonsiliasi agen dan menentukan barang yang akan diberikan,
            // JUGA Owner sudah tutup buku.
            // Selama belum dilakukan itu maka yang masuk ke daftar belanja HANYA barang konsumen yang sudah melewati batas titik aman.
            $isEligibleForShopping = ($scope === 'raw_all')
                ? true
                : ($isSafeReached || ($isBookClosed && $hasOwnerDecision));

            if (!$isEligibleForShopping) {
                // Catat ke daftar konsumen tidak selesai yang belum ditentukan barangnya oleh Owner
                $excludedIncompleteCustomers[] = [
                    'customer_id' => $cp->customer_id,
                    'customer_package_id' => $cp->id,
                    'customer_name' => $custName,
                    'customer_code' => $custCode,
                    'customer_phone' => $custPhone,
                    'customer_address' => $custAddress,
                    'agent_id' => $agentId,
                    'agent_name' => $agentName,
                    'agent_phone' => $agentPhone,
                    'package_id' => $pkg->id,
                    'package_name' => $pkg->name,
                    'package_code' => $pkg->code,
                    'package_type' => $pkg->type,
                    'period_text' => $periodText,
                    'total_target' => (float) ($cp->total_target ?: $pkg->target_nominal),
                    'total_paid' => (float) $cp->total_paid,
                    'progress_percent' => (float) $cp->progress_percent,
                    'safe_deficit' => (float) $cp->safe_deficit_nominal,
                    'consecutive_unpaid' => (int) $cp->consecutive_unpaid_weeks,
                    'stop_status' => $cp->stop_status,
                    'status_label' => $cp->stop_status === 'pending'
                        ? 'Pengajuan Stop Menunggu ACC Owner'
                        : 'Belum Titik Aman (Kurang Rp ' . number_format($cp->safe_deficit_nominal, 0, ',', '.') . ')',
                ];

                continue; // LEWATI DARI DAFTAR BELANJA!
            }

            $eligibleCustomersCount++;

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

            // Update statistik order paket yang masuk belanja
            $packageSummaryMap[$pkgId]['total_orders'] += 1;
            if ($cp->fulfillment_status === 'fulfilled') {
                $packageSummaryMap[$pkgId]['fulfilled_orders'] += 1;
            } else {
                $packageSummaryMap[$pkgId]['pending_orders'] += 1;
            }
            $packageSummaryMap[$pkgId]['total_target_amount'] += (float) ($cp->total_target ?: $pkg->target_nominal);
            $packageSummaryMap[$pkgId]['total_paid_amount'] += (float) $cp->total_paid;

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

            // Label kelayakan masuk belanja
            $eligibilityLabel = $hasOwnerDecision
                ? ($isSafeReached ? 'Lolos Titik Aman (Disesuaikan Owner)' : 'Keputusan Khusus Owner (Alokasi Sah)')
                : 'Lolos Titik Aman (Jaminan Full Paket)';

            // Catat data konsumen pemesan paket
            $packageSummaryMap[$pkgId]['customers'][] = [
                'customer_id' => $cp->customer_id,
                'customer_package_id' => $cp->id,
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
                'is_safe_reached' => $isSafeReached,
                'has_owner_decision' => $hasOwnerDecision,
                'eligibility_label' => $eligibilityLabel,
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

            // 2. REKAP ISI BARANG & UANG DENGAN PEMISAHAN KATEGORI:
            // - Parsel Harian
            // - Parsel Mingguan
            // - Paket Eceran
            // - Hadiah Tabungan
            // - Hadiah Parsel
            // - Rekap Semua Barang (Gabungan)
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
                        if ($pkg->isTabungan()) {
                            $this->accumulateItem($tabunganBonusTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Bonus Tutup Buku)');
                            $this->accumulateItem($allGoodsTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Bonus Tutup Buku)', 'from_tabungan_bonus');
                        } else {
                            $this->accumulateItem($parselBonusTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Bonus Tutup Buku)');
                            $this->accumulateItem($allGoodsTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Bonus Tutup Buku)', 'from_parsel_bonus');
                        }

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
                        if ($pkg->isParsel()) {
                            if ($pkg->isHarian()) {
                                $this->accumulateItem($parselHarianTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Tutup Buku)');
                                $this->accumulateItem($allGoodsTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Tutup Buku)', 'from_parsel_harian');
                            } else {
                                $this->accumulateItem($parselMingguanTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Tutup Buku)');
                                $this->accumulateItem($allGoodsTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Tutup Buku)', 'from_parsel_mingguan');
                            }
                        } elseif ($pkg->isEcer()) {
                            $this->accumulateItem($ecerTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Tutup Buku)');
                            $this->accumulateItem($allGoodsTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Tutup Buku)', 'from_ecer');
                        } elseif ($pkg->isTabungan()) {
                            $this->accumulateItem($tabunganBonusTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Barang Pengganti Tabungan)');
                            $this->accumulateItem($allGoodsTotals, $pfi->item_id, $pfi->quantity, $pkg->name . ' (Barang Pengganti Tabungan)', 'from_tabungan_bonus');
                        }

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
                $isHarian = $pkg->isHarian();

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
                    } elseif ($pi->is_bonus) {
                        $this->accumulateItem($parselBonusTotals, $pi->item_id, $pi->quantity, $pkg->name);
                        $this->accumulateItem($allGoodsTotals, $pi->item_id, $pi->quantity, $pkg->name, 'from_parsel_bonus');

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
                        if ($isHarian) {
                            $this->accumulateItem($parselHarianTotals, $pi->item_id, $pi->quantity, $pkg->name);
                            $this->accumulateItem($allGoodsTotals, $pi->item_id, $pi->quantity, $pkg->name, 'from_parsel_harian');
                        } else {
                            $this->accumulateItem($parselMingguanTotals, $pi->item_id, $pi->quantity, $pkg->name);
                            $this->accumulateItem($allGoodsTotals, $pi->item_id, $pi->quantity, $pkg->name, 'from_parsel_mingguan');
                        }

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
            } elseif ($pkg->isTabungan()) {
                // Tabungan: Payout utama adalah Uang Tunai kepada Nasabah!
                $uangItem = $pkg->packageItems->first(fn($pi) => $pi->item && $pi->item->isUang() && !$pi->is_bonus);
                $tabunganCash = $uangItem ? (float) ($uangItem->item->unit_price * $uangItem->quantity) : (float) ($pkg->target_nominal ?: ($pkg->base_price ?: $cp->total_target));

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
                                $this->accumulateItem($tabunganBonusTotals, $pi->item_id, $pi->quantity, $pkg->name);
                                $this->accumulateItem($allGoodsTotals, $pi->item_id, $pi->quantity, $pkg->name, 'from_tabungan_bonus');

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
                        $this->accumulateItem($ecerTotals, $ci->item_id, $ci->quantity, $pkg->name);
                        $this->accumulateItem($allGoodsTotals, $ci->item_id, $ci->quantity, $pkg->name, 'from_ecer');

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

        // 3. OLAH LAPORAN PER KATEGORI (PARSEL HARIAN, PARSEL MINGGUAN, ECERAN, HADIAH TABUNGAN, GRAND TOTAL SEMUA BARANG)
        $allItemIds = array_unique(array_merge(
            array_keys($parselHarianTotals),
            array_keys($parselMingguanTotals),
            array_keys($ecerTotals),
            array_keys($tabunganBonusTotals),
            array_keys($parselBonusTotals),
            array_keys($allGoodsTotals)
        ));
        $itemsMaster = Item::whereIn('id', $allItemIds)->get()->keyBy('id');

        $parselHarianReport = $this->buildGoodsReport($parselHarianTotals, $itemsMaster);
        $parselMingguanReport = $this->buildGoodsReport($parselMingguanTotals, $itemsMaster);
        $ecerReport = $this->buildGoodsReport($ecerTotals, $itemsMaster);
        $tabunganBonusReport = $this->buildGoodsReport($tabunganBonusTotals, $itemsMaster, 'Hadiah Tabungan');
        $parselBonusReport = $this->buildGoodsReport($parselBonusTotals, $itemsMaster, 'Bonus Parsel');
        $allGoodsReport = $this->buildGoodsReport($allGoodsTotals, $itemsMaster);

        // Gabungan bonus untuk kompatibilitas
        $bonusReport = array_merge($tabunganBonusReport, $parselBonusReport);
        usort($bonusReport, function ($a, $b) {
            $catCmp = strcasecmp($a['category'], $b['category']);
            if ($catCmp !== 0) return $catCmp;
            return strcasecmp($a['item_name'], $b['item_name']);
        });

        $calcTotals = function(array $list) {
            $cost = 0;
            $pieces = 0;
            foreach ($list as $item) {
                $cost += (float)$item['estimated_cost'];
                $pieces += (int)$item['total_base_qty'];
            }
            return ['cost' => $cost, 'pieces' => $pieces, 'unique' => count($list)];
        };

        $harianTotals = $calcTotals($parselHarianReport);
        $mingguanTotals = $calcTotals($parselMingguanReport);
        $ecerSumTotals = $calcTotals($ecerReport);
        $tabunganBonusSumTotals = $calcTotals($tabunganBonusReport);
        $parselBonusSumTotals = $calcTotals($parselBonusReport);
        $bonusSumTotals = $calcTotals($bonusReport);
        $allGoodsSumTotals = $calcTotals($allGoodsReport);

        // Kategori breakdown untuk all goods
        $categoryBreakdown = [];
        foreach ($allGoodsReport as $gr) {
            $cat = $gr['category'] ?: 'Lain-lain';
            if (!isset($categoryBreakdown[$cat])) {
                $categoryBreakdown[$cat] = [
                    'category' => $cat,
                    'total_base_qty' => 0,
                    'estimated_cost' => 0,
                ];
            }
            $categoryBreakdown[$cat]['total_base_qty'] += $gr['total_base_qty'];
            $categoryBreakdown[$cat]['estimated_cost'] += $gr['estimated_cost'];
        }

        // 4. OLAH TABEL KEMASAN / WADAH PARSEL
        uasort($packagingTotals, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        $totalPackagingUnits = 0;
        $totalPackagingCost = 0;
        foreach ($packagingTotals as $kms) {
            $totalPackagingUnits += $kms['total_qty'];
            $totalPackagingCost += $kms['total_cost'];
        }

        // 5. OLAH TABEL PENYIAPAN UANG TUNAI
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

        // 6. OLAH REKAPAN JUMLAH PAKET YANG HARUS DISIAPKAN
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

        uasort($packageSummaryMap, function ($a, $b) {
            if ($a['total_orders'] !== $b['total_orders']) {
                return $b['total_orders'] <=> $a['total_orders'];
            }
            return strcmp($a['package_name'], $b['package_name']);
        });

        $grandTotalFund = $totalCashAmount + $allGoodsSumTotals['cost'] + $totalPackagingCost;

        return [
            'packages_to_prepare' => array_values($packageSummaryMap),
            'items' => $allGoodsReport, // Rekapitulasi belanja seluruh barang pokok
            'all_goods_items' => $allGoodsReport,
            'parsel_harian_items' => $parselHarianReport,
            'parsel_mingguan_items' => $parselMingguanReport,
            'ecer_items' => $ecerReport,
            'tabungan_bonus_items' => $tabunganBonusReport,
            'parsel_bonus_items' => $parselBonusReport,
            'bonus_items' => $bonusReport,
            'packaging_items' => array_values($packagingTotals),
            'cash_items' => $cashRequirements,
            'cash_requirements' => $cashRequirements,
            'cash_by_agent' => array_values($cashByAgent),
            'excluded_incomplete_customers' => $excludedIncompleteCustomers,
            'summary' => [
                'scope' => $scope,
                'scope_label' => $scope === 'raw_all'
                    ? 'Semua Pesanan Konsumen Tanpa Filter (PO Supplier)'
                    : 'Hanya Konsumen Lolos Titik Aman / Ditetapkan Tutup Buku',
                'safe_threshold_reached_count' => $safeThresholdReachedCount,
                'pending_safe_count' => $pendingSafeCount,
                'total_packages_count' => $totalPackagesCount,
                'total_packages_pending' => $totalPackagesPending,
                'total_packages_fulfilled' => $totalPackagesFulfilled,
                'total_parsel_count' => $totalParselCount,
                'total_tabungan_count' => $totalTabunganCount,
                'total_ecer_count' => $totalEcerCount,
                'packages_with_orders_count' => $packagesWithOrdersCount,
                'total_packages_master' => count($packageSummaryMap),

                // Kategori Terpisah Belanja Barang
                'parsel_harian_cost' => $harianTotals['cost'],
                'parsel_harian_pieces' => $harianTotals['pieces'],
                'parsel_harian_unique' => $harianTotals['unique'],

                'parsel_mingguan_cost' => $mingguanTotals['cost'],
                'parsel_mingguan_pieces' => $mingguanTotals['pieces'],
                'parsel_mingguan_unique' => $mingguanTotals['unique'],

                'ecer_cost' => $ecerSumTotals['cost'],
                'ecer_pieces' => $ecerSumTotals['pieces'],
                'ecer_unique' => $ecerSumTotals['unique'],

                'tabungan_bonus_cost' => $tabunganBonusSumTotals['cost'],
                'tabungan_bonus_pieces' => $tabunganBonusSumTotals['pieces'],
                'tabungan_bonus_unique' => $tabunganBonusSumTotals['unique'],

                'parsel_bonus_cost' => $parselBonusSumTotals['cost'],
                'parsel_bonus_pieces' => $parselBonusSumTotals['pieces'],
                'parsel_bonus_unique' => $parselBonusSumTotals['unique'],

                // Rekap Gabungan Semua Barang
                'total_unique_items' => $allGoodsSumTotals['unique'],
                'total_base_pieces' => $allGoodsSumTotals['pieces'],
                'total_estimated_cost' => $allGoodsSumTotals['cost'],

                'total_unique_bonus' => $bonusSumTotals['unique'],
                'total_bonus_pieces' => $bonusSumTotals['pieces'],
                'total_bonus_cost' => $bonusSumTotals['cost'],

                // Kemasan & Uang
                'total_packaging_types' => count($packagingTotals),
                'total_packaging_units' => $totalPackagingUnits,
                'total_packaging_cost' => $totalPackagingCost,

                'total_cash_amount' => $totalCashAmount,
                'total_cash_recipients' => count($cashRequirements),
                'total_cash_agents' => count($cashByAgent),

                'grand_total_goods_fund' => $allGoodsSumTotals['cost'] + $totalPackagingCost,
                'grand_total_fund' => $grandTotalFund,
                'total_eligible_customers' => $eligibleCustomersCount,
                'total_excluded_incomplete' => count($excludedIncompleteCustomers),
                'all_packages_count' => $allPackages->count(),
                'categories' => array_values($categoryBreakdown),
            ],
        ];
    }

    private function accumulateItem(array &$totals, int $itemId, int $qty, string $packageName, string $sourceKey = 'qty'): void
    {
        if (!isset($totals[$itemId])) {
            $totals[$itemId] = [
                'qty' => 0,
                'packages' => [],
                'from_parsel_harian' => 0,
                'from_parsel_mingguan' => 0,
                'from_ecer' => 0,
                'from_tabungan_bonus' => 0,
                'from_parsel_bonus' => 0,
            ];
        }

        $totals[$itemId]['qty'] += $qty;
        if ($sourceKey !== 'qty') {
            $totals[$itemId][$sourceKey] += $qty;
        }
        $totals[$itemId]['packages'][] = $packageName;
    }

    private function buildGoodsReport(array $totals, $itemsMaster, ?string $defaultCategory = null): array
    {
        $report = [];

        foreach ($totals as $itemId => $data) {
            /** @var Item $item */
            $item = $itemsMaster->get($itemId);
            if (!$item) continue;

            $totalBase = (int) $data['qty'];
            if ($totalBase <= 0) continue;

            $rate = $item->conversion_rate > 0 ? (float)$item->conversion_rate : 1;

            if ($rate > 1) {
                $wholesaleFull = floor($totalBase / $rate);
                $wholesaleRemainder = $totalBase - ($wholesaleFull * $rate);
                $wholesaleCeil = (int) ceil($totalBase / $rate);
                $wholesaleUnit = $item->wholesale_unit ?: $item->base_unit;
                $wholesaleText = $wholesaleFull . ' ' . $wholesaleUnit . ($wholesaleRemainder > 0 ? ' + ' . $wholesaleRemainder . ' ' . $item->base_unit : '');
            } else {
                $wholesaleFull = $totalBase;
                $wholesaleRemainder = 0;
                $wholesaleCeil = $totalBase;
                $wholesaleUnit = $item->base_unit;
                $wholesaleText = $totalBase . ' ' . $item->base_unit;
            }

            $costPerUnit = $item->cost_price > 0 ? (float)$item->cost_price : ((float)$item->unit_price * 0.85);
            $estCost = $totalBase * $costPerUnit;
            $cat = $item->category ?: ($defaultCategory ?: 'Lain-lain');

            $report[] = [
                'item_id' => $item->id,
                'item_code' => $item->code,
                'item_name' => $item->name,
                'category' => $cat,
                'base_unit' => $item->base_unit,
                'wholesale_unit' => $wholesaleUnit,
                'conversion_rate' => $rate,
                'total_base_qty' => $totalBase,
                'from_parsel_harian' => $data['from_parsel_harian'] ?? 0,
                'from_parsel_mingguan' => $data['from_parsel_mingguan'] ?? 0,
                'from_ecer' => $data['from_ecer'] ?? 0,
                'from_tabungan_bonus' => $data['from_tabungan_bonus'] ?? 0,
                'from_parsel_bonus' => $data['from_parsel_bonus'] ?? 0,
                'wholesale_full' => (int) $wholesaleFull,
                'wholesale_remainder' => (int) $wholesaleRemainder,
                'wholesale_ceil' => $wholesaleCeil,
                'wholesale_text' => $wholesaleText,
                'cost_price' => $costPerUnit,
                'estimated_cost' => $estCost,
                'packages_list' => array_values(array_unique($data['packages'] ?? [])),
            ];
        }

        // Urutkan barang: Kategori (A-Z), Nama Barang (A-Z)
        usort($report, function ($a, $b) {
            $catCmp = strcasecmp($a['category'], $b['category']);
            if ($catCmp !== 0) return $catCmp;
            return strcasecmp($a['item_name'], $b['item_name']);
        });

        return $report;
    }
}
