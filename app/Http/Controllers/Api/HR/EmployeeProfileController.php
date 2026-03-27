<?php

namespace App\Http\Controllers\Api\HR;

use Illuminate\Http\Request;
use App\Models\Employee_profile;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
class EmployeeProfileController extends Controller
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
        //Store the request data in database
        $validate = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'aadhar_number' => 'required|string|max:12',
            'pan_number' => 'required|string|max:10',
            'employement_type' => 'required|string',
            'degree_name' => 'required|string',
            'college_name' => 'required|string',
            'year_of_passing' => 'required|integer|min:1900|max:' . date('Y'),
            'experience' => 'nullable|integer|min:0'
        ]);

        Employee_profile::create($validate);
        return response()->json([
            'status'=>true,
            "message"=>"Successfully stored the employee profile",
            "data"=> $validate
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
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
        $employee=Employee_profile::where('employee_id',$id)->first();
        if(!$employee){
            return response()->json([
                'status'=>false,
                "message"=>"Employee profile not found"
            ],status:404);
        }
        $validate = $request->validate([
            'aadhar_number' => [
                'required',
                'string',
                'size:12',
                Rule::unique('employee_profiles')->ignore($employee->id)
            ],
            'pan_number' => 'required|string|max:10',
            'employement_type' => 'required|string',
            'degree_name' => 'required|string',
            'college_name' => 'required|string',
            'year_of_passing' => 'required|integer|min:1900|max:' . date('Y'),
            'experience' => 'nullable|integer|min:0'
        ]);
        $employee->update($validate);
        return response()->json([
            'status'=>true,
            "message"=>"Successfully updated the employee profile",
            "data"=>$validate
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $employee=Employee_profile::where('employee_id',$id)->first();
        if(!$employee){
            return response()->json([
                'status'=>false,
                "message"=>"Employee profile not found"
            ],status:404);
        }
        $employee->delete();
        return response()->json([
            'status'=>true,
            "message"=>"Successfully deleted the employee profile"
        ]);
    }
}
