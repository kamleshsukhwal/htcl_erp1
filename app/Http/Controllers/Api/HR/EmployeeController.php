<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'gender' => 'required|string',
            'email_id' => 'required|email|unique:employees,email_id',
            'role' => 'required|string'
        ]);
        $employee = Employee::create([
            'name' => $request->name,
            'gender' => $request->gender,
            'email_id' => $request->email_id,
            'role' => $request->role
        ]);
        if ($employee) {
            $response = [
                'status' => true,
                'message' => 'Employee created successfully',
                'data' => $employee
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Failed to create employee'
            ];
        }
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //Accessing a specific employee by ID
        $employee=Employee::find($id);
        if (!$employee) {
            return response()->json([
                'status' => false,
                "message" => "Employee not found"
            ], status: 404);
        }
        return response()->json([
            'status' => true,
            'Message' => "Employee details",
            'data' => $employee
        ]);
    }
    public function show_withdetails(string $id)
    {
        $employee = Employee::with('details')->find($id);
        if (!$employee) {
            return response()->json([
                'status' => false,
                "message" => "Employee not found"
            ], status: 404);
        }
        return response()->json([
            'status' => true,
            'Message' => "Employee details",
            'data' => $employee
        ]);

    }
    public function show_profile(string $id)
    {
        $employee = Employee::with('profile')->find($id);
        if (!$employee) {
            return response()->json([
                'status' => false,
                "message" => "Employee not found"
            ], status: 404);
        }
        return response()->json([
            'status' => true,
            'Message' => "Employee details",
            'data' => $employee
        ]);

    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                'status' => false,
                "message" => "Employee not found"
            ], status: 404);
        }
        $request->validate([
            'name' => 'required|string',
            'gender' => 'required|string',
            'email_id' => 'required|email|unique:employees,email_id',
            'Role' => 'required|string'
        ]);
        $employee->update($request->all());
        return response()->json([
            'status'=>true,
            'message'=>'Employee update successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $employee=Employee::find($id);
        if(!$employee){
            return response()->json([
                'status'=>false,
                "message"=>'Employee details Not found'
            ]);
        }
        $employee->delete();
        return response()->json([
            'status'=>true,
            "message"=>"Employee details deleted successfully"
        ]);
    }
}
