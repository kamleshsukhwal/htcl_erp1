<?php

namespace App\Http\Controllers\Api\Boq;

use App\Http\Controllers\Controller;
use App\Models\BoqItemProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoqItemProgressController extends Controller
{
   

/*
public function store(Request $request)
{
    $request->validate([
        'boq_item_id' => 'required|exists:boq_items,id',
        'executed_qty' => 'required|numeric|min:0',
        'dc_type' =>'required',
        'dc_no' => 'required',
        'entry_date' => 'required|date'
    ]);

    BoqItemProgress::create($request->all());

    return response()->json(['message' => 'Progress updated']);
}*/



public function store(Request $request)
{
    $validated = $request->validate([
        'boq_item_id' => 'required|exists:boq_items,id',
        'executed_qty' => 'required|numeric|min:0',
        'dc_type' => 'required',
        'dc_no' => 'required',
        'entry_date' => 'required|date'
    ]);

    // Save Progress
    $progress = BoqItemProgress::create($validated);

    // Calculate Total Executed for this BOQ Item
    $totalExecuted = BoqItemProgress::where('boq_item_id', $validated['boq_item_id'])
        ->sum('executed_qty');

    // Get Planned Qty
    $plannedQty = DB::table('boq_items')
        ->where('id', $validated['boq_item_id'])
        ->value('quantity');

    return response()->json([
        'message' => 'Progress updated successfully',
        'data' => [
            'last_entry' => $progress,
            'planned_qty' => $plannedQty ?? 0,
            'total_executed_qty' => $totalExecuted ?? 0,
            'balance_qty' => ($plannedQty ?? 0) - ($totalExecuted ?? 0),
            'progress_percent' => $plannedQty > 0
                ? round(($totalExecuted / $plannedQty) * 100, 2)
                : 0
        ]
    ]);
}





public function show(Request $request)
{
    $query = BoqItemProgress::query();

    // Filter by BOQ Item
    if ($request->boq_item_id) {
        $query->where('boq_item_id', $request->boq_item_id);
    }

    // Date Filters
    if ($request->from_date) {
        $query->whereDate('entry_date', '>=', $request->from_date);
    }

    if ($request->to_date) {
        $query->whereDate('entry_date', '<=', $request->to_date);
    }

    $progressList = $query->latest()->get();
    // Total Executed (filtered result)
    $totalExecuted = $query->sum('executed_qty');

    return response()->json([
        'filters' => [
            'boq_item_id' => $request->boq_item_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date
        ],
        'total_executed_qty' => $totalExecuted ?? 0,
        'count' => $progressList->count(),
        'data' => $progressList
    ]);
}

}
