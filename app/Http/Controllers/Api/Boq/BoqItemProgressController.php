<?php

namespace App\Http\Controllers\Api\Boq;

use App\Http\Controllers\Controller;
use App\Models\BoqItemProgress;
use Illuminate\Http\Request;

class BoqItemProgressController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'boq_item_id' => 'required|exists:boq_items,id',
        'executed_qty' => 'required|numeric|min:0',
        'entry_date' => 'required|date'
    ]);

    BoqItemProgress::create($request->all());

    return response()->json(['message' => 'Progress updated']);
}

}
