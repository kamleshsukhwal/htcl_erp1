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
    // ✅ Normalize items
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
       // 'items.*.boq_item_id' => 'nullable|exists:boq_items,id',
      //  'items.*.item_name' => 'required_without:items.*.boq_item_id',
        'items.*.issued_qty' => 'required|numeric|min:0.01',

        'items.*.boq_item_id' => 'required|exists:boq_items,id',
'items.*.item_name' => 'nullable',
    ]);

    DB::beginTransaction();

    try {

        // ✅ Better DC Number
        $dcNumber = 'DCOUT-' . date('Ymd') . '-' . rand(1000, 9999);

        // ✅ Create DC OUT
        $dcOut = DcOut::create([
            'dc_number' => $dcNumber,
            'project_id' => $request->project_id,
            'issue_date' => $request->issue_date,
            'issued_to' => $request->issued_to,
            'created_by' => auth()->id() ?? null
        ]);

        // ✅ Fetch all BOQ items in one query (performance fix)
        $boqIds = collect($items)->pluck('boq_item_id')->filter()->unique();

        $boqItems = BoqItem::whereIn('id', $boqIds)
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {

    $boqId = $item['boq_item_id'];

    if (!isset($boqItems[$boqId])) {
        throw new \Exception("Invalid BOQ Item ID: " . $boqId);
    }

    $itemNameFinal = $boqItems[$boqId]->item_name;

    // ✅ Save DC OUT item
    DcOutItem::create([
        'dc_out_id' => $dcOut->id,
        'boq_item_id' => $boqId,
        'item_name' => $itemNameFinal,
        'issued_qty' => $item['issued_qty']
    ]);

    // ✅ Stock fetch
    $stock = Stock::where('boq_item_id', $boqId)
        ->lockForUpdate()
        ->first();

    if (!$stock) {
        throw new \Exception("Stock not found for BOQ Item ID: " . $boqId);
    }

    if ($stock->available_qty < $item['issued_qty']) {
        throw new \Exception("Insufficient stock for item: " . $itemNameFinal);
    }

    // ✅ Stock out
    $stock->decrement('available_qty', $item['issued_qty']);

    // ✅ Stock transaction
    StockTransaction::create([
        'boq_item_id' => $boqId,
        'item_name' => $itemNameFinal,
        'type' => 'OUT',
        'quantity' => $item['issued_qty'],
        'reference_type' => 'DC_OUT',
        'reference_id' => $dcOut->id,
        'created_by' => auth()->id() ?? null
    ]);

// ✅ COMMIT AFTER LOOP
DB::commit();
} 
    }catch (\Exception $e) {

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