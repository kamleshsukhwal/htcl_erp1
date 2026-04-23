<?php

namespace App\Http\Controllers\Api\HR;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Http\Controllers\Controller;
class DepartmentsController extends Controller
{
    public function index()
    {
        return Department::all();
    }

    public function show($id)
    {
        $Department = Department::findOrFail($id);
        if(!$Department){
            return response()->json([
                'status' => false,
                'message' => 'Department not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $Department
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string'
        ]);

        return Department::create($request->only('department_name'));
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'department_name' => 'required|string'
        ]);

        $department->update($request->only('department_name'));

        return $department;
    }

    public function destroy($id)
    {
        $department = Department::find($id);
        if(!$department){
            return response()->json([
                'status' => false,
                'message' => 'Department not found'
            ], 404);
        }
        Department::destroy($id);

        return response()->json(['message' => 'Department deleted successfully']);
    }
}
