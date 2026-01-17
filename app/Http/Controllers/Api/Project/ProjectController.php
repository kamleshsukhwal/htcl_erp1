<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ProjectController extends Controller
{
    // 1️⃣ List Projects
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Project::latest()->paginate(10)
        ]);
    }

    // 2️⃣ Store Project
    public function store(Request $request)
    {
        $request->validate([
            'client_id'           => 'required|exists:clients,id',
            'project_name'        => 'required|string|max:255',
            'project_code'        => 'required|string|unique:projects,project_code',
            'project_type'        => 'required|in:Residential,Commercial,Infra',
            'billing_type'        => 'required|in:BOQ,fixed,milestone',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'project_value'       => 'nullable|numeric|min:0',
            'approved_budget'     => 'nullable|numeric|min:0',
            'project_manager_id'  => 'nullable|exists:users,id',
            'assigned_users'      => 'nullable|array',
            'description'         => 'nullable|string',
            'remarks'             => 'nullable|string'
        ]);

        $project = Project::create([
            'client_id'          => $request->client_id,
            'project_code'       => $request->project_code,
            'project_name'       => $request->project_name,
            'project_type'       => $request->project_type,
            'start_date'         => $request->start_date,
            'end_date'           => $request->end_date,
            'project_value'      => $request->project_value,
            'approved_budget'    => $request->approved_budget,
            'actual_cost'        => 0,
            'billing_type'       => $request->billing_type,
            'progress_percent'   => 0,
            'description'        => $request->description,
            'remarks'            => $request->remarks,
            'project_manager_id' => $request->project_manager_id,
            'assigned_users'     => $request->assigned_users,
            'status'             => 'active',
            'created_by'         => Auth::id()
            
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Project created successfully',
            'data' => $project
        ], 201);
    }

    // 3️⃣ Show Single Project
    public function show($id)
    {
        return response()->json([
            'status' => true,
            'data' => Project::findOrFail($id)
        ]);
    }

    // 4️⃣ Update Project (Partial Safe)
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'project_name'        => 'sometimes|required|string|max:255',
            'project_type'        => 'sometimes|required|in:Residential,Commercial,Infra',
            'billing_type'        => 'sometimes|required|in:BOQ,fixed,milestone',
            'start_date'          => 'sometimes|nullable|date',
            'end_date'            => 'sometimes|nullable|date|after_or_equal:start_date',
            'project_value'       => 'sometimes|nullable|numeric|min:0',
            'approved_budget'     => 'sometimes|nullable|numeric|min:0',
            'actual_cost'         => 'sometimes|nullable|numeric|min:0',
            'progress_percent'    => 'sometimes|nullable|integer|min:0|max:100',
            'project_manager_id'  => 'sometimes|nullable|exists:users,id',
            'assigned_users'      => 'sometimes|nullable|array',
            'status'              => 'sometimes|required|in:active,inactive,completed',
            'remarks'             => 'sometimes|nullable|string',
            'description'         => 'sometimes|nullable|string'
        ]);

        $project->update(
            $request->only([
                'project_name',
                'project_type',
                'billing_type',
                'start_date',
                'end_date',
                'project_value',
                'approved_budget',
                'actual_cost',
                'progress_percent',
                'project_manager_id',
                'assigned_users',
                'status',
                'remarks',
                'description'
            ]) + [
             //   'updated_by' => auth()->id()
                'updated_by' => Auth::id()
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Project updated successfully',
            'data' => $project
        ]);
    }

    // 5️⃣ Delete Project
    public function destroy($id)
    {
        Project::findOrFail($id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Project deleted successfully'
        ]);
    }
}
