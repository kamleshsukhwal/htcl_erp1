<?php
namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Project::latest()->paginate(10)
        ]);
    }

  public function store(Request $request)
{
    $request->validate([
        'project_name' => 'required|string',
        'project_code' => 'required|unique:projects,project_code',
        'project_type' => 'required',
        'billing_type' => 'in:boq,fixed,milestone',
        'project_manager_id' => 'nullable|exists:users,id'
    ]);

    $project = Project::create([
        'project_name' => $request->project_name,
        'project_code' => $request->project_code,
        'client_name' => $request->client_name,
        'client_email' => $request->client_email,
        'client_phone' => $request->client_phone,
        'project_type' => $request->project_type,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'project_value' => $request->project_value,
        'approved_budget' => $request->approved_budget,
        'billing_type' => $request->billing_type ?? 'boq',
        'project_manager_id' => $request->project_manager_id,
        'assigned_users' => $request->assigned_users,
        'status' => 'active',
        'created_by' => auth()->id()
    ]);

    return response()->json([
        'status' => true,
        'data' => $project
    ]);
}

    public function show($id)
    {
        return Project::findOrFail($id);
    }

    public function update(Request $request, $id)
{
    $project = Project::findOrFail($id);

    $project->update([
        'progress_percent' => $request->progress_percent,
        'actual_cost' => $request->actual_cost,
        'remarks' => $request->remarks,
        'assigned_users' => $request->assigned_users,
        'project_manager_id' => $request->project_manager_id,
        'status' => $request->status
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Project updated successfully'
    ]);
}


    public function destroy($id)
    {
        Project::findOrFail($id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Project deleted'
        ]);
    }
}
