<?php

namespace App\Http\Controllers\Api\HR;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LeaveType;
class LeaveTypeController extends Controller
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
        $exist = LeaveType::where('name',$request->name)->first();
        if($exist){
            return response()->json([
                'status'=>false,
                "message"=>"Leave type with this name already exists",
            ],400);
        }
        $validate =$request->validate([
            'name'=>'required|string|max:255',
            'max_allowed_days'=>'required|integer|min:0',
            'accural_enabled'=>'boolean',
            'accrual_rate'=>'nullable|numeric|min:0',
            'credit_forward_enabled'=>'boolean',
            'is_paid'=>'boolean',
            'half_day_allowed'=>'boolean'
        ]);

        LeaveType::create($validate);

        return response()->json([
            'status'=>true,
            "message"=>"Successfully created custom Leave type",
            "data"=>$validate
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $exist = LeaveType::where("id",$id)->first();
        if(!$exist){
            return response()->json([
                'status'=>false,
                "message"=>"Leave type with this id does not exist",
            ],400);
            
        }

        return response()->json([
            'status'=>true,
            "message"=>"Successfully retrieved custom Leave type",
            "data"=>$exist
        ]);
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
        $exist =LeaveType::where('name',$request->name)->first();
        if(!$exist){
            return response()->json([
                'status'=>false,
                "message"=>"Leave with this name does not exist",
            ],400);
        }

         $validate =$request->validate([
            'name'=>'required|string|max:255',
            'max_allowed_days'=>'required|integer|min:0',
            'accural_enabled'=>'boolean',
            'accrual_rate'=>'nullable|numeric|min:0',
            'credit_forward_enabled'=>'boolean',
            'is_paid'=>'boolean',
            'half_day_allowed'=>'boolean'
        ]);

        LeaveType::where('id',$id)->update($validate);
        return response()->json([
            'status'=>true,
            "message"=>"Successfully updated custom Leave type",
            "data"=>$validate
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $exist =LeaveType::find($id,'id')->first();
        if(!$exist){
            return response()->json([
                'status'=>false,
                "message"=>"Leave with this name does not exist",
            ],400);
        }
        $exist->delete();
        return response()->json([
            'status'=>true,
            "message"=>"Successfully deleted custom Leave type",
        ]);
    }
}
