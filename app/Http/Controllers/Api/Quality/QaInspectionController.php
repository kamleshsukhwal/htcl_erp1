<?php

namespace App\Http\Controllers\Api\Quality;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QaInspection;
use App\Models\QaInspectionItem;
use App\Models\QaChecklistItem;
use Illuminate\Support\Facades\Storage;

class QaInspectionController extends Controller
{
    // ✅ CREATE
   public function store(Request $request)
{
    $validated = $request->validate([
        'inspections' => 'required|array|min:1',

        'inspections.*.project_id'      => 'required|exists:projects,id',
        'inspections.*.checklist_id'    => 'required|exists:qa_checklists,id',
        'inspections.*.boq_item_id'     => 'required|exists:boq_items,id',
        'inspections.*.vendor_id'       => 'nullable|exists:vendors,id',
        'inspections.*.inspection_date' => 'required|date',
        'inspections.*.location'        => 'nullable|string|max:255',
        'inspections.*.remarks'         => 'nullable|string',
        'inspections.*.inspected_by'    => 'nullable|integer',
    ]);

    $created = [];

    foreach ($validated['inspections'] as $inspectionData) {

        // ✅ 1. Create Inspection Header
        $inspection = QaInspection::create([...$inspectionData,
            'status' => 'draft', // important
        ]);

        // ✅ 2. Fetch Checklist Items
        $checklistItems = QaChecklistItem::where(
            'checklist_id',
            $inspectionData['checklist_id']
        )->get();

        // ✅ 3. Create Inspection Items
        foreach ($checklistItems as $item) {
            QaInspectionItem::create([
                'inspection_id'     => $inspection->id,
                'checklist_item_id' => $item->id,
                'result'            => null,
                'remarks'           => null,
            ]);
        }

        $created[] = $inspection;
    }

    return response()->json([
        'message' => 'Bulk Inspections Created Successfully',
        'data' => $created
    ], 201);
}

    // ✅ LIST
    public function index()
    {
        return QaInspection::with('checklist')
            ->latest()
            ->get();
    }

    // ✅ SHOW
    public function show($id)
{
    $inspection = QaInspection::with([
        'project',
        'checklist',
        'boqItem',
        'vendor',
        'inspector'
    ])->findOrFail($id);

    return response()->json([
        'message' => 'Inspection details fetched',
        'data' => $inspection
    ]);
}
    
    
    public function destroy($id)
    {
        $inspection = QaInspection::findOrFail($id);
        $inspection->delete();

        return response()->json([
            'message' => 'Inspection deleted successfully'
        ]);
    }

public function submit(QaInspection $inspection)
{
    if ($inspection->status !== 'draft') {
        return response()->json(['message' => 'Only draft inspections can be submitted'], 400);
    }

    $inspection->update([
        'status' => 'submitted'
    ]);

    return response()->json([
        'message' => 'Inspection submitted successfully',
        'data' => $inspection
    ]);
}

/*** approval */

public function approve(QaInspection $inspection)
{
    if ($inspection->status !== 'submitted') {
        return response()->json(['message' => 'Only submitted inspections can be approved'], 400);
    }

    $inspection->update([
        'status' => 'approved'
    ]);

    return response()->json([
        'message' => 'Inspection approved',
        'data' => $inspection
    ]);
}

/** Reject**/

public function reject(Request $request, QaInspection $inspection)
{
    $inspection->update([
        'status' => 'rejected',
        'remarks' => $request->remarks
    ]);

    return response()->json([
        'message' => 'Inspection rejected',
        'data' => $inspection
    ]);
}


public function updateResult(Request $request, QaInspectionItem $item)
{
    $validated = $request->validate([
        'result' => 'required|string|max:255',
        'remarks' => 'nullable|string'
    ]);

    $item->update($validated);

    return response()->json([
        'message' => 'Inspection item updated',
        'data' => $item
    ]);
}

 
    /*****  inspection item  */  
    
public function addResult(Request $request, QaInspection $inspection)
{
    $validated = $request->validate([
        'checklist_item_id' => 'required|exists:qa_checklist_items,id',
        'result' => 'required|string|max:255',
        'remarks' => 'nullable|string'
    ]);

    // Prevent duplicate result
    $exists = QaInspectionItem::where('inspection_id', $inspection->id)
                ->where('checklist_item_id', $validated['checklist_item_id'])
                ->exists();

    if ($exists) {
        return response()->json([
            'message' => 'Result already exists for this checklist item'
        ], 400);
    }

    $item = $inspection->items()->create([
        'checklist_item_id' => $validated['checklist_item_id'],
        'result' => $validated['result'],
        'remarks' => $validated['remarks'] ?? null,
    ]);

    return response()->json([
        'message' => 'Inspection result added successfully',
        'data' => $item
    ], 201);
}


public function items(QaInspection $inspection)
{
    return response()->json([
        'data' => $inspection->items()->with('checklistItem')->get()
    ]);
}

public function upload(Request $request, $id)
{
    $request->validate([
        'file' => 'required|file|max:5120', // 5MB
    ]);

    $inspection = QaInspection::findOrFail($id);

    $file = $request->file('file');

    $path = $file->store('inspection_attachments', 'public');

    // Save path in database (if column exists)
    $inspection->attachment = $path;
    $inspection->save();

    return response()->json([
        'message' => 'File uploaded successfully',
        'path' => $path
    ]);
}
}