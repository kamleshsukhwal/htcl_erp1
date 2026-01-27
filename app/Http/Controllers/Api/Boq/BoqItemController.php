<?php

namespace App\Http\Controllers\Api\Boq;

use App\Models\BoqItem;
use App\Models\BoqItemHistory;
use App\Models\Boq;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BoqItemController extends Controller
{
    /**
     * Bulk update BOQ items with history
     */
    public function bulkUpdateItems(Request $request, $boqId)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:boq_items,id',
        ]);

        DB::transaction(function () use ($request, $boqId) {

            foreach ($request->items as $row) {

                $item = BoqItem::where('id', $row['id'])
                    ->where('boq_id', $boqId)
                    ->firstOrFail();

                // 🟡 Capture OLD values
                $oldQty  = $item->quantity;
                $oldRate = $item->rate;

                // 🟢 New values (fallback to old)
                $newQty  = $row['quantity'] ?? $item->quantity;
                $newRate = $row['rate'] ?? $item->rate;

                // ✅ Store history ONLY if changed
                if ($oldQty != $newQty || $oldRate != $newRate) {
                    BoqItemHistory::create([
                        'boq_id'        => $item->boq_id,
                        'boq_item_id'   => $item->id,
                        'old_quantity'  => $oldQty,
                        'new_quantity'  => $newQty,
                        'old_rate'      => $oldRate,
                        'new_rate'      => $newRate,
                        'changed_by'    => auth()->id(),
                        'change_date'   => now()->toDateString()
                    ]);
                }

                // 🔵 Update live BOQ item
                $item->update([
                    'sn'            => $row['sn'] ?? $item->sn,
                    'description'   => $row['description'] ?? $item->description,
                    'unit'          => $row['unit'] ?? $item->unit,
                    'quantity'      => $newQty,
                    'rate'          => $newRate,
                    'total_amount'  => $newQty * $newRate,
                    'scope'         => $row['scope'] ?? $item->scope,
                    'approved_make' => $row['approved_make'] ?? $item->approved_make,
                    'offered_make'  => $row['offered_make'] ?? $item->offered_make,
                ]);
            }

            // 🔄 Recalculate totals for BOQ and parent project
            $this->recalculateBoqAndProject($boqId);
        });

        return response()->json([
            'status' => true,
            'message' => 'BOQ items updated successfully with history'
        ]);
    }

    /**
     * Recalculate BOQ total and Project total
     */
    private function recalculateBoqAndProject($boqId)
    {
        // 1️⃣ Recalculate BOQ total
        $boqTotal = BoqItem::where('boq_id', $boqId)
            ->sum(DB::raw('quantity * rate'));

        // 2️⃣ Update BOQ total
        $boq = Boq::find($boqId);
        if ($boq) {
            $boq->total_amount = $boqTotal;
            $boq->save();

            // 3️⃣ Recalculate project total if BOQ is linked to a project
            if ($boq->project_id) {
                $projectTotal = Boq::where('project_id', $boq->project_id)
                    ->sum('total_amount');

                $project = Project::find($boq->project_id);
                if ($project) {
                    $project->total_amount = $projectTotal;
                    $project->save();
                }
            }
        }
    }


    
public function historyByDate(Request $request)
{
    $request->validate([
        'from_date' => 'required|date',
        'to_date'   => 'nullable|date|after_or_equal:from_date',
        'boq_item_id' => 'nullable|exists:boq_items,id',
    ]);

    $query = BoqItemHistory::query();

    // 🔹 Optional filter by BOQ item
    if ($request->filled('boq_item_id')) {
        $query->where('boq_item_id', $request->boq_item_id);
    }

    // 🔹 Date filter
    if ($request->filled('to_date')) {
        $query->whereBetween('created_at', [
            $request->from_date,
            $request->to_date
        ]);
    } else {
        $query->whereDate('created_at', $request->from_date);
    }

    $data = $query
        ->orderBy('created_at', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'status' => true,
        'count'  => $data->count(),
        'data'   => $data
    ]);
}
}
