<?php

namespace App\Http\Controllers\Api\Inventory;
use Illuminate\Support\Facades\DB;  
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\Stock;

class StockController extends Controller
{
    public function index()
{
    $stocks = Stock::with('boqItem')->get();

    $data = $stocks->map(function ($stock) {

        return [
            'id' => $stock->id,

            // ✅ FIX: show correct item name
            'item_name' => $stock->boqItem->item_name 
                ?? $stock->item_name 
                ?? '-',

            'available_qty' => $stock->available_qty,

            // ✅ UNIT FIX
            'unit' => $stock->boqItem->unit ?? '-',

            // ✅ STATUS
            'status' => $stock->available_qty > 5 ? 'Available' : 'Low Stock'
        ];
    });

    return response()->json([
        'status' => true,
        'data' => $data
    ]);
}
public function show(Request $request)
{
    $query = Stock::with('boqItem');

    if ($request->boq_item_id) {
        $query->where('boq_item_id', $request->boq_item_id);
    }

    if ($request->item_name) {
        $query->where('item_name', $request->item_name);
    }

    $stock = $query->first();

    if (!$stock) {
        return response()->json([
            'status' => false,
            'message' => 'Stock not found'
        ], 404);
    }

    return response()->json([
        'status' => true,
        'data' => [
            'id' => $stock->id,
            'item_name' => $stock->boqItem->item_name 
                ?? $stock->item_name 
                ?? '-',
            'available_qty' => $stock->available_qty,
            'unit' => $stock->boqItem->unit ?? '-'
        ]
    ]);
}

public function ledger(Request $request)
{
    $query = DB::table('stock_transactions');

    if ($request->boq_item_id) {
        $query->where('boq_item_id', $request->boq_item_id);
    }

    if ($request->item_name) {
        $query->where('item_name', $request->item_name);
    }

    $data = $query->orderBy('id', 'desc')->get();

    return response()->json([
        'status' => true,
        'count' => $data->count(),
        'data' => $data
    ]);
}
public function lowStock()
{
    $stocks = Stock::with('boqItem')
        ->whereColumn('available_qty', '<', 'min_qty')
        ->get();

    $data = $stocks->map(function ($stock) {

        return [
            'id' => $stock->id,
            'item_name' => $stock->boqItem->item_name 
                ?? $stock->item_name 
                ?? '-',
            'available_qty' => $stock->available_qty,
            'unit' => $stock->boqItem->unit ?? '-'
        ];
    });

    return response()->json([
        'status' => true,
        'count' => count($data),
        'data' => $data
    ]);
}
}