<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee_detail;
use Illuminate\Http\Request;

class EmployeeDetailController extends Controller
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
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id|unique:employee_details,id',
            'dob' => 'required|date',
            'photo' => 'required|string',
            'address' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_name' => 'required|string',
            'ifsc_code' => 'required|string',
            'contact_number' => 'required|string',
        ]);

        $employeeDetail = Employee_detail::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Employee details created successfully',
            'data' => $employeeDetail
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    // public function show(Employee_detail $employee_detail)
    // {

    // }
    public function show(string $employee_id)
    {
        $employee_detail = Employee_detail::where('employee_id', $employee_id)->first();
        if (!$employee_detail) {
            return response()->json([
                'status' => false,
                'message' => "Employee detail not found"
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Employee detail found',
            'data' => $employee_detail
        ], 200);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee_detail $employee_detail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $employee_id)
    {
        $employee = Employee_detail::where('employee_id', $employee_id)->first();
        if (!$employee) {
            return response()->json([
                'status' => false,
                "message" => "Employee not found"
            ], status: 404);
        }
        $request->validate([
            'dob' => 'required|date',
            'photo' => 'required|string',
            'address' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_name' => 'required|string',
            'ifsc_code' => 'required|string',
            'contact_number' => 'required|string',
        ]);
        $employee->update($request->all());
        return response()->json([
            'status' => true,
            'message' => 'Employee update successfully',
            'data'=>$employee
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $employee_id)
    {
        //
        $employee=Employee_detail::where('employee_id',$employee_id)->first();
        if(!$employee){
            return response()->json([
                'status'=>false,
                "message"=>"Employee not found"
            ],404);
        }
        $employee->delete();
        return response()->json([
            "status"=>true,
            "message"=>"Employee deleted successfully"
        ]);
    }
}
