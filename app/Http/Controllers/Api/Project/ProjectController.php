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
            'project_name' => 'required',
            'client_name' => 'required',
            'start_date' => 'required|date'
        ]);

        $project = Project::create([
            'project_code' => 'PRJ-' . time(),
            'project_name' => $request->project_name,
            'client_name' => $request->client_name,
            'client_email' => $request->client_email,
            'client_phone' => $request->client_phone,
            'project_type' => $request->project_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'project_value' => $request->project_value,
            'status' => 'active',
            'description' => $request->description,
           // 'created_by' => auth()->id()
            'created_by' => 43
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Project created successfully',
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
        $project->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Project updated'
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
