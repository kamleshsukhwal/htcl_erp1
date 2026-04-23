<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\BoqItemProgress;
use App\Models\DcIn;
use App\Models\DcInItem;
use App\Models\Stock;
use App\Models\BoqItem;
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
        $item['boq_id'] = (!empty($boqId) && $boqId != 0) ? (int)$boqId : null;

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

        // ✅ Fetch BOQ item names
        $boqIds = collect($items)->pluck('boq_id')->filter();
        $boqItems = BoqItem::whereIn('id', $boqIds)->pluck('item_name', 'id');

        foreach ($items as $item) {

            $boqId = $item['boq_id'];
            $itemName = $item['item_name'] ?? null;

            // ✅ Final item name
            $itemNameFinal = $boqId
                ? ($boqItems[$boqId] ?? null)
                : $itemName;

            // ✅ Save DC Item
            DcInItem::create([
                'dc_in_id' => $dc->id,
                'boq_item_id' => $boqId,
                'item_name' => $itemNameFinal,
                'supplied_qty' => $item['qty'],
            ]);

            // ✅ Stock Update
            if ($boqId) {
                $stock = Stock::firstOrCreate(
                    ['boq_item_id' => $boqId],
                    ['available_qty' => 0]
                );
            } else {
                $stock = Stock::firstOrCreate(
                    [
                        'item_name' => $itemNameFinal,
                        'boq_item_id' => null
                    ],
                    ['available_qty' => 0]
                );
            }

            $stock->increment('available_qty', $item['qty']);

            // ✅ Stock Transaction
            StockTransaction::create([
                'boq_item_id' => $boqId,
                'item_name' => $itemNameFinal,
                'type' => 'IN',
                'quantity' => $item['qty'],
                'reference_type' => 'DC_IN',
                'reference_id' => $dc->id
            ]);

            // ✅ BOQ Progress
            if ($boqId) {
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

        // =========================
        // 🔥 UPDATE PO STATUS (FINAL FIX)
        // =========================

       $totalOrdered = \App\Models\PurchaseOrderItem::where('purchase_order_id', $request->po_id)
    ->sum('ordered_qty');

$totalReceived = \App\Models\DcInItem::whereHas('dcIn', function ($q) use ($request) {
    $q->where('purchase_order_id', $request->po_id);
})->sum('supplied_qty');

// ✅ FINAL STATUS LOGIC
if ($totalReceived == 0) {
    $status = 'pending';
} elseif ($totalReceived < $totalOrdered) {
    $status = 'approved';
} else {
    $status = 'completed';
}

\App\Models\PurchaseOrder::where('id', $request->po_id)
    ->update(['status' => $status]);
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

    // ===============================
    // ✅ DCIN ITEMS WITH STOCK (FIXED)
    // ===============================
    public function getDcinItemsWithStock($dcinId)
{
    $items = DcInItem::where('dc_in_id', $dcinId)
        ->get()
        ->map(function ($item) {

            if ($item->boq_item_id) {

                $stock = Stock::where('boq_item_id', $item->boq_item_id)->first();

            } else {

                $stock = Stock::where('item_name', $item->item_name)
                    ->whereNull('boq_item_id')
                    ->first();
            }

            return [
               // 'dcin_item_id' => $item->id,
             //   'boq_item_id' => $item->boq_item_id,
                'item_name' => $stock->item_name ?? $item->item_name ?? 'N/A', // ✅ FIXED
                'dcin_qty' => $item->supplied_qty,
                'available_qty' => $stock->available_qty ?? 0
            ];
        });

    return response()->json([
        'status' => true,
        'data' => $items
    ]);
}




public function dropdownfordcout()
{
    $data = DcIn::select('id', 'dc_number')
        ->withCount('items') // 🔥 counts dc_in_items
        ->latest()
        ->get();

    return response()->json([
        'status' => true,
        'data' => $data
    ]);
}
}