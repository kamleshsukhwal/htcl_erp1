<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\BoqItemProgress;
use App\Models\DcIn;
use App\Models\DcInItem;
use App\Models\Stock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DcInController extends Controller
{
    public function store(Request $request)
    {
        // ✅ Normalize items
        $items = collect($request->items)->map(function ($item) {

            $boqId = $item['boq_id'] ?? null;

            if (empty($boqId) || $boqId == 0 || $boqId === "0") {
                $item['boq_id'] = null;
            } else {
                $item['boq_id'] = (int) $boqId;
            }

            return $item;

        })->toArray();

        $request->merge(['items' => $items]);

        // ✅ Validation
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'po_id' => 'required|exists:purchase_orders,id',
            'delivery_channel' => 'required|in:vendor,warehouse,site',
            'items' => 'required|array',
            'items.*.boq_id' => 'nullable|exists:boq_items,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.item_name' => 'required_without:items.*.boq_id',
        ]);

        DB::transaction(function () use ($request, $items) {

            // ✅ Create DC IN
            $dc = DcIn::create([
                'dc_number' => 'DC' . time(),
                'vendor_id' => $request->vendor_id,
                'purchase_order_id' => $request->po_id,
                'delivery_channel' => $request->delivery_channel,
                'delivery_date' => now(),
            ]);

            foreach ($items as $item) {

                $boqId = $item['boq_id'] ?? null;
                $itemName = $item['item_name'] ?? null;

                // =========================
                // ✅ SAVE DC ITEM
                // =========================
                DcInItem::create([
                    'dc_in_id' => $dc->id,
                    'boq_item_id' => $boqId,
                    'item_name' => $itemName,
                    'supplied_qty' => $item['qty'],
                ]);

                // =========================
                // ✅ STOCK LOGIC (COMMON)
                // =========================
                if (!empty($boqId)) {

                    // 🔹 BOQ ITEM
                    $stock = Stock::firstOrCreate(
                        ['boq_item_id' => $boqId],
                        ['available_qty' => 0]
                    );

                } else {

                    // 🔹 MANUAL ITEM
                    $stock = Stock::firstOrCreate(
                        ['item_name' => $itemName],
                        ['available_qty' => 0]
                    );
                }

                // 🔥 Increment stock
                $stock->increment('available_qty', $item['qty']);

                // =========================
                // ✅ STOCK TRANSACTION
                // =========================
                StockTransaction::create([
                    'boq_item_id' => $boqId,
                    'item_name' => $boqId ? null : $itemName,
                    'type' => 'IN',
                    'quantity' => $item['qty'],
                    'reference_type' => 'DC_IN',
                    'reference_id' => $dc->id
                ]);

                // =========================
                // ✅ PROGRESS (ONLY BOQ)
                // =========================
                if (!empty($boqId)) {

                    BoqItemProgress::updateOrCreate(
                        [
                            'boq_item_id' => $boqId,
                            'entry_date' => now()->toDateString()
                        ],
                        [
                            'executed_qty' => DB::raw("executed_qty + {$item['qty']}")
                        ]
                    );
                }
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'DC IN saved successfully'
        ]);
    }

    // ===============================
    // ✅ LIST DC IN
    // ===============================
    public function index(Request $request)
    {
        $query = DcIn::with(['items.boqItem']);

        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->purchase_order_id) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }

        if ($request->from_date) {
            $query->whereDate('delivery_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('delivery_date', '<=', $request->to_date);
        }

        $dcList = $query->latest()->paginate(10);

        return response()->json([
            'status' => true,
            'count' => $dcList->total(),
            'data' => $dcList
        ]);
    }

    // ===============================
    // ✅ SHOW SINGLE DC IN
    // ===============================
    public function show($id)
    {
        $dc = DcIn::with(['items.boqItem'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $dc
        ]);
    }
}