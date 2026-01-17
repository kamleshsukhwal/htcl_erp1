<?php

namespace App\Http\Controllers\Api\Boq;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\BoqFile;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

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
            'message' => 'BOQ status updated'
        ]);
    }

    // 🔹 SHOW FULL BOQ (HEADER + ITEMS + FILES)
    public function show($id)
    {
        $boq = Boq::with(['items', 'files'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $boq
        ]);
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
            'file_type'  => $file->getClientOriginalExtension(),
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
}
