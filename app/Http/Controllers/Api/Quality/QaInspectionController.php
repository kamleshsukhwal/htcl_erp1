<?php

namespace App\Http\Controllers\Api\Quality;

use App\Http\Controllers\Controller;
use App\Models\QaInspection;
use Illuminate\Http\Request;
use App\Models\QaInspectionItem;
class QaInspectionController extends Controller
{
    // ✅ CREATE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'      => 'required|exists:projects,id',
            'checklist_id'    => 'required|exists:qa_checklists,id',
            'boq_item_id'     => 'nullable|exists:boq_items,id',
            'vendor_id'       => 'nullable|exists:vendors,id',
            'inspection_date' => 'required|date',
            'location'        => 'nullable|string|max:255',
            'remarks'         => 'nullable|string',
            'inspected_by'    => 'nullable|integer'
        ]);

        $inspection = QaInspection::create($validated);

        return response()->json([
            'message' => 'Inspection Created Successfully',
            'data' => $inspection
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
    public function show(QaInspection $inspection)
    {
        $inspection->load('checklist');

        return response()->json([
            'message' => 'Inspection details fetched',
            'data' => $inspection
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
}