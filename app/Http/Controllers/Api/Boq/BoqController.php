<?php

namespace App\Http\Controllers\Api\Boq;


use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\BoqFile;
use App\Models\Project;
use App\import\BoqItemsImport;
use App\Imports\BoqItemsImport as ImportsBoqItemsImport;
use App\Models\BoqItemHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; 

class BoqController extends Controller
{
    // 🔹 LIST BOQS BY PROJECT
    public function listByProject(Request $request, $projectId)
    {
        $query = Boq::where('project_id', $projectId);

        if ($request->discipline) {
            $query->where('discipline', $request->discipline);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $boqs = $query->withCount('items')->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $boqs
        ]);
    }

    // 🔹 CREATE BOQ HEADER
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'boq_name'   => 'required|string',
            'discipline' => 'required|string'
        ]);

        $boq = Boq::create([
            'project_id'   => $request->project_id,
            'boq_name'     => $request->boq_name,
            'discipline'   => $request->discipline,
            'status'       => 'draft',
            'total_amount' => 0,
            'created_by'   => Auth::id()
        ]);

        return response()->json([
            'status' => true,
            'data' => $boq
        ], 201);
    }

    // 🔹 ADD BOQ ITEMS (BULK)
    public function addItems(Request $request, $boqId)
    {
        $request->validate([
            'items' => 'required|array'
        ]);

        foreach ($request->items as $row) {
            BoqItem::create([
                'boq_id'        => $boqId,
                'sn'            => $row['sn'] ?? null,
                'description'   => $row['description'],
                'unit'          => $row['unit'] ?? null,
                'quantity'      => $row['quantity'] ?? 0,
                'rate'          => $row['rate'] ?? 0,
                'total_amount'  => ($row['quantity'] ?? 0) * ($row['rate'] ?? 0),
                'scope'         => $row['scope'] ?? null,
                'approved_make' => $row['approved_make'] ?? null,
                'offered_make'  => $row['offered_make'] ?? null,
            ]);
        }

        $this->recalculateBoqAndProject($boqId);

        return response()->json([
            'status' => true,
            'message' => 'BOQ items added successfully'
        ]);
    }

    // 🔹 UPDATE BOQ ITEM
    public function updateItem(Request $request, $itemId)
    {
        $item = BoqItem::findOrFail($itemId);

        $request->validate([
            'description'    => 'sometimes|string',
            'unit'           => 'sometimes|string', // remove nullable if DB not nullable
            'quantity'       => 'sometimes|numeric',
            'rate'           => 'sometimes|numeric',
            'scope'          => 'sometimes|nullable|string',
            'approved_make'  => 'sometimes|nullable|string',
            'offered_make'   => 'sometimes|nullable|string',
        ]);

        $item->update([
            'sn' => $request->sn,
            'description' => $request->description,
            'unit' => $request->unit,
            'quantity' => $request->quantity,
            'rate' => $request->rate,
            'total_amount' => $request->quantity * $request->rate,
            'scope' => $request->scope,
            'approved_make' => $request->approved_make,
            'offered_make' => $request->offered_make,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'BOQ item updated successfully',
            'data' => $item
        ]);
    }


    // 🔹 DELETE BOQ ITEM
    public function deleteItem($itemId)
    {
        $item = BoqItem::findOrFail($itemId);
        $boqId = $item->boq_id;

        $item->delete();

        $this->recalculateBoqAndProject($boqId);

        return response()->json([
            'status' => true,
            'message' => 'BOQ item deleted'
        ]);
    }

    // 🔹 UPDATE BOQ STATUS
    public function updateStatus(Request $request, $boqId)
    {
        $request->validate([
            'status' => 'required|in:draft,submitted,approved'
        ]);

        Boq::where('id', $boqId)->update([
            'status' => $request->status
        ]);

        return response()->json([
            'status' => true,
            'message' => 'BOQ status updated' // update item  file
        ]);
    }
/// BOQ item upload from file.





public function getBoqById($boqId)
{
    $boq = Boq::with([
            'items' => function ($q) {
                $q->orderBy('sn');
            },
            'files'
        ])
        ->findOrFail($boqId);

    return response()->json([
        'status' => true,
        'data' => $boq
    ]);
}

// JSON upload
public function itemsByBoq($boqId)
{
    $items = BoqItem::where('boq_id', $boqId)
        ->orderBy('sn')
        ->get(); ///  file lock by admin

    return response()->json([
        'status' => true,
        'data' => $items
    ]);
}



    // 🔹 SHOW FULL BOQ (HEADER + ITEMS + FILES)
    public function show($id)
    {
        $boq = Boq::with(['items', 'files'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $boq
        ]); /// use composer json upload  package
    }

    // 🔹 UPLOAD BOQ FILE
    public function uploadFile(Request $request, $boqId)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,pdf|max:10240'
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());


        $path = $file->storeAs("boqs/$boqId", $fileName, 'public');

        BoqFile::create([
            'boq_id'     => $boqId,
            'file_name'  => $fileName,
            'file_path'  => $path,
            'file_type'  => $file->getClientOriginalExtension(), // should be .csv or .excel, .xls
            'uploaded_by' => Auth::id()

          
        ]);

        return response()->json([
            'status' => true,
            'message' => 'File uploaded successfully'
        ]);
    }

    // 🔁 COMMON FUNCTION – RECALCULATE TOTALS
    private function recalculateBoqAndProject($boqId)
    {
        $boqTotal = BoqItem::where('boq_id', $boqId)->sum('total_amount');

        $boq = Boq::find($boqId);
        $boq->update(['total_amount' => $boqTotal]);

        $projectTotal = Boq::where('project_id', $boq->project_id)->sum('total_amount');

        Project::where('id', $boq->project_id)->update([
            'actual_cost' => $projectTotal
        ]);
    }

/*  code is working but to make history commented*/
    public function bulkUpdateItems(Request $request, $boqId)
{
    $request->validate([
        'items' => 'required|array|min:1',
        'items.*.id' => 'required|exists:boq_items,id',
        'items.*.quantity' => 'nullable|numeric',
        'items.*.rate' => 'nullable|numeric',
    ]);

    DB::transaction(function () use ($request, $boqId) {

        foreach ($request->items as $row) {

            $item = BoqItem::where('id', $row['id'])
                ->where('boq_id', $boqId)
                ->firstOrFail();

            $quantity = $row['quantity'] ?? $item->quantity;
            $rate     = $row['rate'] ?? $item->rate;

            $item->update([
                'sn'            => $row['sn'] ?? $item->sn,
                'description'   => $row['description'] ?? $item->description,
                'unit'          => $row['unit'] ?? $item->unit,
                'quantity'      => $quantity,
                'rate'          => $rate,
                'total_amount'  => $quantity * $rate,
                'scope'         => $row['scope'] ?? $item->scope,
                'approved_make' => $row['approved_make'] ?? $item->approved_make,
                'offered_make'  => $row['offered_make'] ?? $item->offered_make,
            ]);
        }

        $this->recalculateBoqAndProject($boqId);
    });

    return response()->json([
        'status' => true,
        'message' => 'BOQ items updated successfully'
    ]);
}
 
public function importBoq(Request $request, Boq $boq)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,csv'
    ]);

    // Store file
    $path = $request->file('file')->store('imports');

    // Absolute path
    $fullPath = storage_path('app/' . $path);

    // 🔍 Debug safety check
    if (!file_exists($fullPath)) {
        logger()->error('File not saved', [
            'path' => $fullPath
        ]);
        return response()->json(['error' => 'File upload failed'], 500);
    }

    (new ImportsBoqItemsImport())->import($fullPath, $boq->id);

    return response()->json([
        'status' => true,
        'message' => 'BOQ imported successfully'
    ]);
}

/**** bulk update */
public function bulkUpdate(Request $request)
{
    DB::transaction(function () use ($request) {

        foreach ($request->items as $item) {

            $boqItem = BoqItem::findOrFail($item['id']);

            // 1️⃣ Store history
            BoqItemHistory::create([
                'boq_item_id' => $boqItem->id,
                'boq_id'      => $boqItem->boq_id,
                'old_quantity'=> $boqItem->quantity,
                'new_quantity'=> $item['quantity'],
                'old_rate'    => $boqItem->rate,
                'new_rate'    => $item['rate'] ?? $boqItem->rate,
                'changed_by'  => auth()->id(),
                'change_reason' => 'Manual BOQ edit'
            ]);

            // 2️⃣ Update live BOQ item
            $boqItem->update([
                'quantity' => $item['quantity'],
                'rate'     => $item['rate'] ?? $boqItem->rate,
                'amount'   => $item['quantity'] * ($item['rate'] ?? $boqItem->rate)
            ]);
        }
    });

    return response()->json([
        'message' => 'BOQ items updated successfully'
    ]);
}

}
