<?php

namespace App\Http\Controllers\Api\HR;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Employee_department;
use App\Models\Employee;

class EmployeeDepartmentController extends Controller
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
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = request()->validate([
            'employee_id' => 'required|exists:employees,id',
            'department_id' => 'required|exists:department,id',
        ]);

        if ($validatedData) {
            $employeeDepartment = Employee_department::create($validatedData);
            return response()->json([
                'message' => 'Employee department created successfully',
                'data' => $employeeDepartment
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Department = Employee_department::where('department_id', $id)->get();

        if ($Department->isEmpty()) {
            return response()->json([
                'message' => 'No employees found for the specified department',
                'data' => []
            ], 404);
        }

        return response()->json(
            $Department->map(function ($employeeDepartment) {
                return [
                    'employee_id' => $employeeDepartment->employee_id,
                    'employee_name' => $employeeDepartment->employee->name,
                    'department_id' => $employeeDepartment->department_id,
                    'department_name' => $employeeDepartment->department->department_name,
                ];
            })
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'new_department_id' => 'required|exists:department,id',
            'old_department_id' => 'required|exists:department,id',
        ]);

        $record = Employee_department::where('employee_id', $id)
            ->where('department_id', $validatedData['old_department_id'])
            ->first();

        if (!$record) {
            $newRecord = Employee_department::create([
                'employee_id' => $id,
                'department_id' => $validatedData['new_department_id']
            ]);

            return response()->json([
                'message' => 'Old mapping not found, new one created',
                'data' => $newRecord
            ], 201); // better than 404
        }

        $record->update([
            'department_id' => $validatedData['new_department_id']
        ]);

        return response()->json([
            'message' => 'Employee department updated successfully',
            'data' => $record
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $ids = $request->query('ids');
        if (!$ids) {
            return response()->json([
                'message' => 'No IDs provided for deletion'
            ], 400);
        }

        // $idsArray = explode(',',$ids);
        $employeeId=$ids[0];
        $departmentId=$ids[1];

        $record = Employee_department::where('employee_id', $employeeId)
            ->where('department_id', $departmentId)
            ->first();

        if(!$record){
            return response()->json([
                'message' => 'record not found'
            ], 404);
        }

        $record->delete();
        return response()->json([
            'message' => 'Employee department deleted successfully'
        ], 200);
    }
}
