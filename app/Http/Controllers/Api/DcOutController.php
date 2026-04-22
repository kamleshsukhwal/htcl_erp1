<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DcOut;
use App\Models\DcOutItem;
use App\Models\Stock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
   use App\Models\BoqItem;
class DcOutController extends Controller
{
    public function store(Request $request)
    {
        // ✅ Normalize items (same as DCIN)
        $items = collect($request->items)->map(function ($item) {

            $boqId = $item['boq_item_id'] ?? null;

            if (empty($boqId) || $boqId == 0 || $boqId === "0") {
                $item['boq_item_id'] = null;
            } else {
                $item['boq_item_id'] = (int) $boqId;
            }

            return $item;

        })->toArray();

        $request->merge(['items' => $items]);

        // ✅ Validation
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'issue_date' => 'required|date',
            'issued_to' => 'required|string',
            'items' => 'required|array',
            'items.*.boq_item_id' => 'nullable|exists:boq_items,id',
            'items.*.item_name' => 'required_without:items.*.boq_item_id',
            'items.*.issued_qty' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {

            // ✅ Create DC OUT
            $dcOut = DcOut::create([
                'dc_number' => 'DCOUT-' . time(),
                'project_id' => $request->project_id,
                'issue_date' => $request->issue_date,
                'issued_to' => $request->issued_to
            ]);

        

foreach ($items as $item) {

    $boqId = $item['boq_item_id'] ?? null;
    $itemName = $item['item_name'] ?? null;

    // ✅ FINAL NAME
    $itemNameFinal = $boqId
        ? optional(BoqItem::find($boqId))->item_name
        : $itemName;

    // =========================
    // ✅ SAVE DC OUT ITEM
    // =========================
    DcOutItem::create([
        'dc_out_id' => $dcOut->id,
        'boq_item_id' => $boqId,
        'item_name' => $itemNameFinal, // ✅ IMPORTANT
        'issued_qty' => $item['issued_qty']
    ]);

    // =========================
    // ✅ STOCK FETCH
    // =========================
    if ($boqId) {
        $stock = Stock::where('boq_item_id', $boqId)->first();
    } else {
        $stock = Stock::where('item_name', $itemNameFinal)
            ->whereNull('boq_item_id')
            ->first();
    }

    if (!$stock || $stock->available_qty < $item['issued_qty']) {
        throw new \Exception("Insufficient stock for item: " . $itemNameFinal);
    }

    // =========================
    // ✅ STOCK OUT
    // =========================
    $stock->decrement('available_qty', $item['issued_qty']);

    // =========================
    // ✅ STOCK TRANSACTION
    // =========================
    StockTransaction::create([
        'boq_item_id' => $boqId,
        'item_name' => $itemNameFinal,
        'type' => 'OUT',
        'quantity' => $item['issued_qty'],
        'reference_type' => 'DC_OUT',
        'reference_id' => $dcOut->id
    ]);
}

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'DC OUT Created Successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }






    // ===============================
    // ✅ LIST ALL DC OUT
    // ===============================
    public function index()
    {
        $data = DcOut::with('items.boqItem')->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    // ===============================
    // ✅ GET ITEMS OF DC OUT
    // ===============================
    public function items($dcOutId)
    {
        $items = DcOutItem::where('dc_out_id', $dcOutId)
            ->with('boqItem')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $items
        ]);
    }

    // ===============================
    // ✅ SHOW SINGLE DC OUT
    // ===============================
    public function show($id)
    {
        $dc = DcOut::with('items.boqItem')->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $dc
        ]);
    }
}