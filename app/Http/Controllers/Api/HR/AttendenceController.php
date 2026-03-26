<?php

namespace App\Http\Controllers\Api\HR;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Attendence;
use App\Models\Employee;

class AttendenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Attendence::with('Employee:id,name')
            ->whereDate('Attendence_date', today())
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Attendence data retrieved successfully',
            'data' => $data
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,$employee_id)
    {


    }
    public function check_in(Request $request,$employee_id)
    {
        $attendence=Attendence::where('employee_id',$employee_id)
                    ->whereDate('Attendence_date',today())->first();
        
        if($attendence){
            return response()->json([
                'status'=>false,
                'message'=>'Already checked-in for today',
            ],200);
        }

        Attendence::create([
            'employee_id'=>$employee_id,
            'Attendence_date'=>today(),
            'In_time'=>now()
        ]);

        return response()->json([
            'status'=>true,
            "message"=>"succesfully checked in"
        ]);
    }

    public function check_out(Request $request,$employee_id)
    {
        $attendence=Attendence::where('employee_id',$employee_id)
                    ->whereNull('Out_time')->first();
        
        if(!$attendence){
            return response()->json([
                'status'=>false,
                'message'=>'Already checked-out for today or not checked in yet',
            ],200);
        }

        $attendence->update([
            'Out_time'=>now()
        ]);

        return response()->json([
            'status'=>true,
            "message"=>"succesfully checked out"
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
