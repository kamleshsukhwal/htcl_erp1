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
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'po_id' => 'required|exists:purchase_orders,id',
            'delivery_channel' => 'required|in:vendor,warehouse,site',
            'items' => 'required|array',
            'items.*.boq_id' => 'nullable|exists:boq_items,id',
            'items.*.qty' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request) {

            // ✅ Create DC IN
            $dc = DcIn::create([
                'dc_number' => 'DC' . time(),
                'vendor_id' => $request->vendor_id,
                'purchase_order_id' => $request->po_id,
                'delivery_channel' => $request->delivery_channel,
                'delivery_date' => now(),
            ]);

             foreach ($request->items as $item) {
 if (isset($item['boq_id']) && $item['boq_id'] == 0) {
        $request->items[$key]['boq_id'] = null;
    }
    $boqId = $item['boq_id'] ?? null;

    // ✅ CASE 1: BOQ ITEM
    if (!empty($boqId)) {

        // Save DC IN Item
        DcInItem::create([
            'dc_in_id' => $dc->id,
            'boq_item_id' => $boqId,
            'item_name' => null, // optional
            'supplied_qty' => $item['qty'],
        ]);

        // 🔥 STOCK UPDATE
        Stock::updateOrCreate(
            ['boq_item_id' => $boqId],
            ['available_qty' => DB::raw("available_qty + {$item['qty']}")]
        );

        // 🔥 STOCK TRANSACTION
        StockTransaction::create([
            'boq_item_id' => $boqId,
            'type' => 'IN',
            'quantity' => $item['qty'],
            'reference_type' => 'DC_IN',
            'reference_id' => $dc->id
        ]);

        // 🔥 PROGRESS TABLE
        BoqItemProgress::updateOrCreate(
            [
                'boq_item_id' => $boqId,
                'entry_date' => now()->toDateString()
            ],
            [
                'executed_qty' => DB::raw("executed_qty + {$item['qty']}")
            ]
        );

    } else {

        // ✅ CASE 2: MANUAL ITEM

        DcInItem::create([
            'dc_in_id' => $dc->id,
            'boq_item_id' => null,
            'item_name' => $item['item_name'], // REQUIRED
            'supplied_qty' => $item['qty'],
        ]);

        // ❌ NO STOCK UPDATE
        // ❌ NO STOCK TRANSACTION
        // ❌ NO PROGRESS TABLE
    }
}
        });

        return response()->json([
            'status' => true,
            'message' => 'DC IN saved successfully'
        ]);  
    }

    /*
    |--------------------------------------------------------------------------
    | LIST ALL DC IN (INDEX)
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | SHOW SINGLE DC IN
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $dc = DcIn::with(['items.boqItem'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $dc
        ]);
    }
}