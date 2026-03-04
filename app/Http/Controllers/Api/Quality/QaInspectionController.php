<?php

namespace App\Http\Controllers\Api\Quality;

use App\Http\Controllers\Controller;
use App\Models\QaInspection;
use Illuminate\Http\Request;
use App\Models\QaInspectionItem;
use App\Models\AuditLog;

class QaInspectionController extends Controller
{
    // ✅ CREATE

    /*
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'      => 'required|exists:projects,id',
            // 'checklist_id'    => 'required|exists:qa_checklists,id',
            'boq_item_id'     => 'nullable|exists:boq_items,id',
            'vendor_id'       => 'nullable|exists:vendors,id',
            'inspection_date' => 'required|date',
            'location'        => 'nullable|string|max:255',
            'remarks'         => 'nullable|string',
            'inspected_by'    => 'nullable|integer'
        ]);

        $inspection = QaInspection::create($validated);

        $inspection->update([
            'status' => 'submitted'
        ]);

     $this->logAudit(
    'inspection',
    $inspection->id,
    'submitted',
    null,
    $inspection->toArray()
);


        return response()->json([
            'message' => 'Inspection Created Successfully',
            'data' => $inspection
        ], 201);
    }



    */

    public function store(Request $request)
{
    $request->validate([
        'inspections' => 'required|array',
        'inspections.*.project_id' => 'required|exists:projects,id',
        'inspections.*.boq_item_id' => 'nullable|exists:boq_items,id',
        'inspections.*.vendor_id' => 'nullable|exists:vendors,id',
        'inspections.*.inspection_date' => 'required|date',
        'inspections.*.location' => 'nullable|string|max:255',
        'inspections.*.remarks' => 'nullable|string',
        'inspections.*.inspected_by' => 'nullable|integer',
    ]);

    $createdInspections = [];

    foreach ($request->inspections as $data) {
        $inspection = QaInspection::create($data);

        $createdInspections[] = $inspection;

        // Audit Log
        $this->logAudit(
            'inspection',
            $inspection->id,
            'created',
            null,
            $inspection->toArray()
        );
    }

    return response()->json([
        'message' => 'Inspections created successfully',
        'data' => $createdInspections
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


        $inspection->update([
            'status' => 'submitted'
        ]);

        $old = $inspection->getOriginal();

$inspection->update(['status' => 'submitted']);

$this->logAudit(
    'inspection',
    $inspection->id,
    'submitted',
    $old,
    $inspection->fresh()->toArray()
);
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
        $inspection->update([
            'status' => 'approved'
        ]);

      $old = $inspection->getOriginal();

$inspection->update(['status' => 'approved']);

$this->logAudit(
    'inspection',
    $inspection->id,
    'approved',
    $old,
    $inspection->fresh()->toArray()
);
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



$old = $inspection->getOriginal();

$inspection->update([
    'status' => 'rejected',
    'remarks' => $request->remarks
]);

$this->logAudit(
    'inspection',
    $inspection->id,
    'rejected',
    $old,
    $inspection->fresh()->toArray()
);
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

        $this->logAudit(
            'inspection',
            $item->id,
            $item->project_id,
            'result_added',
            'Checklist item result added'
        );
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

        $this->logAudit(
            'inspection',
            $inspection->id,
            $inspection->project_id,
            'result_added',
            'Checklist item result added'
        );

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



    /***** Store audit Log */

    private function logAudit($module, $recordId, $action, $oldData = null, $newData = null)
{
    AuditLog::create([
        'module_name'  => $module,
        'record_id'    => $recordId,
        'action'       => $action,
        'old_data'     => $oldData ? json_encode($oldData) : null,
        'new_data'     => $newData ? json_encode($newData) : null,
        'performed_by' => auth()->id(),
    ]);
}
}
