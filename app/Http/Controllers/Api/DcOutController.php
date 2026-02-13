<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DcOut;
use App\Models\DcOutItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DcOutController extends Controller
{
    public function store(Request $request)
{
    DB::beginTransaction();

    try {

        $dcOut = DcOut::create([
            'dc_number' => 'DCOUT-' . time(),
            'project_id' => $request->project_id,
            'issue_date' => $request->issue_date,
            'issued_to' => $request->issued_to
        ]);

        foreach ($request->items as $item) {

            // STOCK CHECK
            $stock = $this->getStock($item['boq_item_id']);

            if ($item['issued_qty'] > $stock) {
                throw new \Exception("Stock not available");
            }

            DcOutItem::create([
                'dc_out_id' => $dcOut->id,
                'boq_item_id' => $item['boq_item_id'],
                'issued_qty' => $item['issued_qty']
            ]);
        }

        DB::commit();

        return response()->json(['message' => 'DC OUT Created']);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 400);
    }
}

private function getStock($boqItemId)
{
    $totalIn = DB::table('dc_in_items')
        ->where('boq_item_id', $boqItemId)
        ->sum('supplied_qty');

    $totalOut = DB::table('dc_out_items')
        ->where('boq_item_id', $boqItemId)
        ->sum('issued_qty');

    return $totalIn - $totalOut;
}

public function index()
{
    return DcOut::with('items')->latest()->get();
}
public function items($dcOutId)
{
    return DcOutItem::where('dc_out_id', $dcOutId)->get();
}

public function show($id)
{
    return DcOut::with('items')->findOrFail($id);
}


}
