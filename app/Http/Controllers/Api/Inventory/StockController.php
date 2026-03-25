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
        $stock = Stock::with('boqItem')->get();

        return response()->json([
            'status' => true,
            'data' => $stock
        ]);
    }

    public function show($boq_item_id)
    {
        $stock = Stock::where('boq_item_id', $boq_item_id)
            ->with('boqItem')
            ->first();

        if (!$stock) {
            return response()->json([
                'status' => false,
                'message' => 'Stock not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $stock
        ]);
    }


    public function ledger($boq_item_id)
{
    $data = DB::table('stock_transactions')
        ->where('boq_item_id', $boq_item_id)
        ->orderBy('id', 'desc')
        ->get();

    return response()->json([
        'status' => true,
        'count' => $data->count(),
        'data' => $data
    ]);
}


public function lowStock()
{
    $data = Stock::with('boqItem')
        ->whereColumn('available_qty', '<', 'min_qty')
        ->get();

    return response()->json([
        'status' => true,
        'count' => $data->count(),
        'data' => $data
    ]);
}
}