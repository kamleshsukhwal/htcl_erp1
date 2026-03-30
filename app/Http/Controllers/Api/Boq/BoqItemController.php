<?php

namespace App\Http\Controllers\Api\Boq;

use App\Models\BoqItem;
use App\Models\BoqItemHistory;
use App\Models\Boq;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\BoqItemFile;
use App\Models\BoqFile;
use Illuminate\Support\Facades\Storage;


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
        'from_date'   => 'required|date',
        'to_date'     => 'nullable|date|after_or_equal:from_date',
        'boq_item_id' => 'nullable|exists:boq_items,id',
    ]);

    $query = BoqItemHistory::query();

    // 🔹 Optional filter by BOQ item
    if ($request->filled('boq_item_id')) {
        $query->where('boq_item_histories.boq_item_id', $request->boq_item_id);
    }

    // 🔹 Date filter
    if ($request->filled('to_date')) {
        $query->whereBetween('boq_item_histories.created_at', [
            $request->from_date,
            $request->to_date
        ]);
    } else {
        $query->whereDate('boq_item_histories.created_at', $request->from_date);
    }

    $data = $query
        ->leftJoin('boq_items', 'boq_items.id', '=', 'boq_item_histories.boq_item_id')
        ->select(
            'boq_item_histories.*',
            'boq_items.item_name as item_name'
        )
        ->orderBy('boq_item_histories.created_at', 'desc')
        ->get();

    return response()->json([
        'status' => true,
        'count'  => $data->count(),
        'data'   => $data
    ]);
}


 
public function uploadItemFile(Request $request, $itemId)
{
    $request->validate([
        'file' => 'required|file|max:10240',
        'document_type' => 'required|string'
    ]);

    $file = $request->file('file');

    $fileName = time().'_'.$file->getClientOriginalName();

    $path = $file->storeAs("boq_items/$itemId", $fileName, 'private');

    $record = BoqItemFile::create([
        'boq_item_id' => $itemId,
        'file_name' => $fileName,
        'file_path' => $path,
        'file_type' => $file->getClientOriginalExtension(),
        'document_type' => $request->document_type,
        'uploaded_by' => auth()->id()
    ]);

    return response()->json([
        'status' => true,
        'message' => 'BOQ item file uploaded successfully',
        'data' => $record
    ]);
}

public function getItemFiles($itemId)
{
    $files = BoqItemFile::where('boq_item_id', $itemId)->get();

    $files->transform(function ($file) {     
        $file->file_url = url('api/boq/boq-item-files/view/' . $file->id);
        return $file;
    });

    return response()->json([
        'status' => true,
        'count' => $files->count(),
        'data' => $files
    ]);

}



    /**** Store BOQ items */ public function store(Request $request, $boqId)
{
    $request->validate([
        'items' => 'required|array|min:1',

        'items.*.item_name'     => 'required|string|max:255',
        'items.*.quantity'      => 'required|numeric|min:0',
        'items.*.rate'          => 'required|numeric|min:0',

        'items.*.sn'            => 'nullable|string|max:50',
        'items.*.description'   => 'nullable|string',
        'items.*.unit'          => 'nullable|string|max:50',
        'items.*.scope'         => 'nullable|string|max:100',
        'items.*.approved_make' => 'nullable|string|max:255',
        'items.*.offered_make'  => 'nullable|string|max:255',
    ]);

    $createdItems = [];

    DB::transaction(function () use ($request, $boqId, &$createdItems) {

        foreach ($request->items as $row) {

            $qty  = $row['quantity'];
            $rate = $row['rate'];

            $item = BoqItem::create([
                'boq_id'        => $boqId, // ✅ always from URL
                'sn'            => $row['sn'] ?? null,
                'item_name'     => $row['item_name'],
                'description'   => $row['description'] ?? null,
                'unit'          => $row['unit'] ?? null,
                'quantity'      => $qty,
                'rate'          => $rate,
                'total_amount'  => $qty * $rate, // ✅ backend calculation
                'scope'         => $row['scope'] ?? null,
                'approved_make' => $row['approved_make'] ?? null,
                'offered_make'  => $row['offered_make'] ?? null,
            ]);

            $createdItems[] = $item;
        }

        // ✅ update totals
        $this->recalculateBoqAndProject($boqId);
    });

    return response()->json([
        'status'  => true,
        'message' => 'BOQ items created successfully',
        'count'   => count($createdItems),
        'data'    => $createdItems
    ]);
}
     public function viewFile($id)
    {
        $file =BoqItemFile::where('id' , $id)->orderBy('id','desc')->first();

        $path = Storage::disk('private')->path($file->file_path);

        if (!file_exists($path)) {
            return response()->json([
                'status' => false,
                'message' => 'File not found'
            ], 404);
        }

        return response()->file($path);
    }

    // ✅ Download file
    public function downloadFile($id)
    {
        $file = BoqItemFile::where('id' , $id)->orderBy('id','desc')->first();

        if (!Storage::disk('private')->exists($file->file_path)) {
            return response()->json([
                'status' => false,
                'message' => 'File not found'
            ], 404);
        }

        return Storage::disk('private')->download(
            $file->file_path,
            $file->file_name
        );
    }
}
