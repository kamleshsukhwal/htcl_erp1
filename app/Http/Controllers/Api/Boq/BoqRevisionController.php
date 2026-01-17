<?php

namespace App\Http\Controllers\Api\Boq;

use App\Http\Controllers\Controller;
use App\Models\Boq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoqRevisionController extends Controller
{
    public function revise($boqId)
{
    $oldBoq = Boq::with('items')->findOrFail($boqId);

    if ($oldBoq->is_locked) {
        return response()->json(['message' => 'BOQ already locked'], 400);
    }

    DB::transaction(function () use ($oldBoq) {

        $newBoq = $oldBoq->replicate();
        $newBoq->revision_no = $oldBoq->revision_no + 1;
        $newBoq->parent_boq_id = $oldBoq->id;
        $newBoq->is_locked = false;
        $newBoq->save();

        foreach ($oldBoq->items as $item) {
            $newItem = $item->replicate();
            $newItem->boq_id = $newBoq->id;
            $newItem->save();
        }

        $oldBoq->update(['is_locked' => true]);
    });

    return response()->json(['message' => 'BOQ revised successfully']);
}

}
