<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Standarisasi satuan pada tabel package_items
        // TEH PUCUK di paket eceran harus bersatuan pcs, bukan liter
        DB::table('package_items')
            ->where('item_id', 9)
            ->where('unit', 'liter')
            ->update(['unit' => 'pcs']);

        // BERAS 20 Kg typo 'pc' distandarisasi ke 'pcs'
        DB::table('package_items')
            ->where('unit', 'pc')
            ->update(['unit' => 'pcs']);

        // 2. Migrasi data lama di customer_custom_items ke item spesifik bersatuan pcs
        // Item 122: MINYAK GORENG
        // - Cicilan 6.000 (12 Ltr) -> Item 190 (MINYAK GORENG 12 Ltr)
        $m12 = DB::table('customer_custom_items')
            ->where('item_id', 122)
            ->where('unit_price', 6000)
            ->get();
        foreach ($m12 as $row) {
            $newQty = max(1, (int) round($row->quantity / 12));
            DB::table('customer_custom_items')
                ->where('id', $row->id)
                ->update([
                    'item_id' => 190,
                    'quantity' => $newQty,
                    'unit' => 'pcs',
                ]);
        }

        // - Cicilan 3.500 (5 Ltr) -> Item 111 (MINYAK GORENG 5 Ltr)
        $m5 = DB::table('customer_custom_items')
            ->where('item_id', 122)
            ->where('unit_price', 3500)
            ->get();
        foreach ($m5 as $row) {
            $newQty = max(1, (int) round($row->quantity / 5));
            DB::table('customer_custom_items')
                ->where('id', $row->id)
                ->update([
                    'item_id' => 111,
                    'quantity' => $newQty,
                    'unit' => 'pcs',
                ]);
        }

        // - Cicilan 2.750 (4 Ltr) -> Item 192 (MINYAK GORENG 4 Ltr)
        $m4 = DB::table('customer_custom_items')
            ->where('item_id', 122)
            ->where('unit_price', 2750)
            ->get();
        foreach ($m4 as $row) {
            $newQty = max(1, (int) round($row->quantity / 4));
            DB::table('customer_custom_items')
                ->where('id', $row->id)
                ->update([
                    'item_id' => 192,
                    'quantity' => $newQty,
                    'unit' => 'pcs',
                ]);
        }

        // - Cicilan 1.500 (2 Ltr) -> Item 191 (MINYAK GORENG 2 Ltr)
        $m2 = DB::table('customer_custom_items')
            ->where('item_id', 122)
            ->where('unit_price', 1500)
            ->get();
        foreach ($m2 as $row) {
            $newQty = max(1, (int) round($row->quantity / 2));
            DB::table('customer_custom_items')
                ->where('id', $row->id)
                ->update([
                    'item_id' => 191,
                    'quantity' => $newQty,
                    'unit' => 'pcs',
                ]);
        }

        // Item 121: BERAS
        // - Cicilan 11.700 (25 Kg) -> Item 110 (BERAS 25 Kg)
        $b25 = DB::table('customer_custom_items')
            ->where('item_id', 121)
            ->where('unit_price', 11700)
            ->get();
        foreach ($b25 as $row) {
            $newQty = max(1, (int) round($row->quantity / 25));
            DB::table('customer_custom_items')
                ->where('id', $row->id)
                ->update([
                    'item_id' => 110,
                    'quantity' => $newQty,
                    'unit' => 'pcs',
                ]);
        }

        // - Cicilan 10.000 (20 Kg) -> Item 88 (BERAS 20 Kg)
        $b20 = DB::table('customer_custom_items')
            ->where('item_id', 121)
            ->where('unit_price', 10000)
            ->get();
        foreach ($b20 as $row) {
            $newQty = max(1, (int) round($row->quantity / 20));
            DB::table('customer_custom_items')
                ->where('id', $row->id)
                ->update([
                    'item_id' => 88,
                    'quantity' => $newQty,
                    'unit' => 'pcs',
                ]);
        }

        // - Cicilan 5.000 (10 Kg) -> Item 64 (BERAS 10 Kg)
        $b10 = DB::table('customer_custom_items')
            ->where('item_id', 121)
            ->where('unit_price', 5000)
            ->get();
        foreach ($b10 as $row) {
            $newQty = max(1, (int) round($row->quantity / 10));
            DB::table('customer_custom_items')
                ->where('id', $row->id)
                ->update([
                    'item_id' => 64,
                    'quantity' => $newQty,
                    'unit' => 'pcs',
                ]);
        }

        // - Cicilan 2.500 (5 Kg) -> Item 100 (BERAS 5 Kg)
        $b5 = DB::table('customer_custom_items')
            ->where('item_id', 121)
            ->where('unit_price', 2500)
            ->get();
        foreach ($b5 as $row) {
            $newQty = max(1, (int) round($row->quantity / 5));
            DB::table('customer_custom_items')
                ->where('id', $row->id)
                ->update([
                    'item_id' => 100,
                    'quantity' => $newQty,
                    'unit' => 'pcs',
                ]);
        }

        // Item 114: DAGING SAPI -> Item 65 (DAGING SAPI 1 Kg)
        DB::table('customer_custom_items')
            ->where('item_id', 114)
            ->update([
                'item_id' => 65,
                'unit' => 'pcs',
            ]);

        // Item 115: TULANG IGA -> Item 185 (TULANG IGA 1 Kg)
        DB::table('customer_custom_items')
            ->where('item_id', 115)
            ->update([
                'item_id' => 185,
                'unit' => 'pcs',
            ]);

        // Item 116: HATI SAPI -> Item 186 (HATI SAPI 1 Kg)
        DB::table('customer_custom_items')
            ->where('item_id', 116)
            ->update([
                'item_id' => 186,
                'unit' => 'pcs',
            ]);

        // Item 117: KAKI SAPI -> Item 187 (KAKI SAPI 1 Kg)
        DB::table('customer_custom_items')
            ->where('item_id', 117)
            ->update([
                'item_id' => 187,
                'unit' => 'pcs',
            ]);

        // Item 118: PARU -> Item 188 (PARU 1 Kg)
        DB::table('customer_custom_items')
            ->where('item_id', 118)
            ->update([
                'item_id' => 188,
                'unit' => 'pcs',
            ]);

        // Item 119: AYAM -> Item 66 (AYAM 1 Kg)
        DB::table('customer_custom_items')
            ->where('item_id', 119)
            ->update([
                'item_id' => 66,
                'unit' => 'pcs',
            ]);

        // Item 120: TELUR -> Item 189 (TELUR 1 Kg)
        DB::table('customer_custom_items')
            ->where('item_id', 120)
            ->update([
                'item_id' => 189,
                'unit' => 'pcs',
            ]);

        // Item 9: TEH PUCUK di custom items distandarisasi ke pcs jika ada yang liter
        DB::table('customer_custom_items')
            ->where('item_id', 9)
            ->where('unit', 'liter')
            ->update(['unit' => 'pcs']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible if needed
    }
};
