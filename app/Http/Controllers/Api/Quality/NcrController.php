<?php

namespace App\Http\Controllers\Api\Quality;

use App\Http\Controllers\Controller;
use App\Models\Ncr;
use App\Models\QaAttachment;
use Illuminate\Http\Request;

class NcrController extends Controller
{
    /* =========================================
       CREATE NCR
    ========================================= */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'     => 'required|exists:projects,id',
            'inspection_id'  => 'nullable|exists:qa_inspections,id',
            'title'          => 'required|string|max:255',
            'issue_description'    => 'required|string',
            'boq_item_id'    => 'nullable|integer',
            'severity'       => 'required|in:low,medium,high',
            'assigned_to'    => 'nullable|exists:users,id',
            'due_date'       => 'nullable|date'
        ]);

        $validated['status'] = 'open';
        $validated['reported_by'] = auth()->id();

        $ncr = Ncr::create($validated);

        return response()->json([
            'message' => 'NCR Created Successfully',
            'data' => $ncr
        ], 201);
    }




/* =========================================
   UPDATE NCR
========================================= */
public function update(Request $request, Ncr $ncr)
{
    $validated = $request->validate([
        'title' => 'sometimes|string|max:255',
        'issue_description' => 'sometimes|string',
        'severity' => 'sometimes|in:low,medium,high',
        'due_date' => 'nullable|date',
        'boq_item_id' => 'nullable|integer'
    ]);

    $ncr->update($validated);

    return response()->json([
        'message' => 'NCR Updated Successfully',
        'data' => $ncr
    ]);
}

    
    /* =========================================
       LIST NCR
    ========================================= */
    public function index()
    {
        return response()->json([
            'data' => Ncr::latest()->paginate(10)
        ]);
    }

    /* =========================================
       SHOW NCR
    ========================================= */
    public function show(Ncr $ncr)
    {
        return response()->json([
            'data' => $ncr
        ]);
    }

    /* =========================================
       ASSIGN NCR
    ========================================= */
    public function assign(Request $request, Ncr $ncr)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);

        $ncr->update([
            'assigned_to' => $request->assigned_to,
            'status' => 'assigned'
        ]);

        return response()->json(['message' => 'NCR Assigned']);
    }

    /* =========================================
       MARK IN PROGRESS
    ========================================= */
    public function markInProgress(Ncr $ncr)
    {
        $ncr->update([
            'status' => 'in_progress'
        ]);

        return response()->json(['message' => 'NCR In Progress']);
    }

    /* =========================================
       MARK CORRECTED
    ========================================= */
    public function markCorrected(Request $request, Ncr $ncr)
    {
        $ncr->update([
            'status' => 'corrected',
            'correction_notes' => $request->correction_notes
        ]);

        return response()->json(['message' => 'NCR Corrected']);
    }

    /* =========================================
       CLOSE NCR
    ========================================= */
    public function close(Request $request, Ncr $ncr)
    {
        $ncr->update([
            'status' => 'closed',
            'closed_remarks' => $request->closed_remarks
        ]);

        return response()->json(['message' => 'NCR Closed']);
    }

    /* =========================================
       UPLOAD ATTACHMENT
    ========================================= */
    public function upload(Request $request, Ncr $ncr)
    {
        $request->validate([
            'file' => 'required|file|max:2048'
        ]);

        $path = $request->file('file')->store('qa', 'public');

        QaAttachment::create([
            'module' => 'ncr',
            'module_id' => $ncr->id,
            'file_path' => $path
        ]);

        return response()->json(['message' => 'File Uploaded']);
    }

    /* =========================================
       FILTER BY PROJECT
    ========================================= */
    public function byProject($projectId)
    {
        $ncrs = Ncr::where('project_id', $projectId)->latest()->get();

        return response()->json([
            'data' => $ncrs
        ]);
    }
}