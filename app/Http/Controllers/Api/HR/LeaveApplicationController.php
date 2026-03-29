<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveApplication;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class LeaveApplicationController extends Controller
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
        $validate = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_type,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string'
        ]);

        $leave_type = LeaveType::find($validate['leave_type_id']);
        $start_date = Carbon::parse($validate['start_date']);
        $end_date = Carbon::parse($validate['end_date']);
        $requestedDays = $start_date->diffInDays($end_date) + 1;
        $leave_balance = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)->first();
        if ($leave_balance == null) {
            $remaining_leave = $leave_type->max_allowed_days;
        } else {

            $remaining_leave = $leave_type->max_allowed_days - $leave_balance->used_leave;
        }
        if ($requestedDays > $remaining_leave) {
            return response()->json([
                'status' => false,
                "message" => "Requested number of days exceeds the maximum allowed for this leave type. Max allowed: " . $leave_type->max_allowed_days . " days. and you had already used " . $leave_balance->used_leave . " leave days. And you are requesting for " . $requestedDays . " days. So you have only " . $remaining_leave . " leave days remaining. cannot submit the Leave application"
            ], 400);
        } else {
            LeaveApplication::create($validate);
            return response()->json([
                'status' => true,
                "message" => "Successfully submitted leave application",
                "data" => $validate
            ]);
        }
    }

    /**
     * Display the specified resource.
     */

    public function mearge_employee_leave_tables(Request $request, $id)
    {
        $employee = LeaveApplication::with(['employee', 'leavetype'])->where('employee_id', $id)->get()
            ->map(function ($leave) {
                return [
                    'employee_name' => $leave->employee->name,
                    'leave_type' => $leave->leavetype->name,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    "reason" => $leave->reason
                ];
            });

        if (!$employee) {
            return response()->json([
                'status' => false,
                "message" => "Employee not found",
            ], 404);
        }
        return response()->json([
            'status' => true,
            "message" => "Successfully fetched employee leave applications",
            "data" => $employee
        ]);
    }
    public function show_leave_type(Request $request, $id)
    {
        $LeaveType = LeaveApplication::with('leavetype')->where('leave_type_id', $id)->get();


        if ($LeaveType->isEmpty()) {
            return response()->json([
                'status' => false,
                "message" => "No such leave type policy found"
            ], 404);
        }
        return response()->json([
            "status" => true,
            "message" => "Successfully fetched the leave type details",
            "data" => $LeaveType,
        ]);
    }

    public function accept_or_reject_application(Request $request, $id)
    {
        $dicision = filter_var($request->is_accpeted, FILTER_VALIDATE_BOOLEAN);

        $Leave_application = LeaveApplication::find($id);
        if (!$Leave_application) {
            return response()->json([
                'status' => false,
                "message" => "Leave_application record not found"
            ], 404);
        }
        if ($Leave_application->status == 'pending') {
            if ($dicision) {
                // Calculate number of days for leave
                $start_date = Carbon::parse($Leave_application->start_date);
                $end_date = Carbon::parse($Leave_application->end_date);
                $requestedDays = $start_date->diffInDays($end_date) + 1; // +1 to include both start and end dates

                // Get leave type details to fetch max_allowed_days
                $leave_type = LeaveType::find($Leave_application->leave_type_id);

                DB::transaction(function () use ($Leave_application, $leave_type, $requestedDays) {

                    $leave_balance = LeaveBalance::lockForUpdate()
                        ->where('employee_id', $Leave_application->employee_id)
                        ->where('leave_type_id', $Leave_application->leave_type_id)
                        ->first();

                    if (!$leave_balance) {
                        $leave_balance = LeaveBalance::create([
                            'employee_id' => $Leave_application->employee_id,
                            'leave_type_id' => $Leave_application->leave_type_id,
                            'max_allowed' => $leave_type->max_allowed_days,
                            'used_leave' => 0
                        ]);
                    }

                    $remaining = $leave_balance->max_allowed - $leave_balance->used_leave;

                    if ($remaining < $requestedDays) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Insufficient leave balance. Remaining: ' . $remaining . ' days. cannot approve the Leave application'
                        ]);
                    }

                    $leave_balance->increment('used_leave', $requestedDays);
                    $Leave_application->update([
                        'status' => 'approved'
                    ]);
                });
            } else {
                // Update leave application status to rejected
                $Leave_application->update([
                    'status' => 'rejected'
                ]);
            }
            return response()->json([
                'status' => true,
                "message" => "Leave application " . ($dicision ? "approved" : "rejected") . " successfully",
            ], 200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Leave application already been processed cannot be approved or rejected again"
            ], 400);
        }
    }
    //Show the Application with status pending on the manager dashboard and then manager accpt or reject the application
    public function show_pending_applications()
    {
        $pending_application = LeaveApplication::with(['employee', 'leavetype'])->where('status', 'pending')->get()
            ->map(function ($leave) {
                return [
                    'Employee_name' => $leave->employee->name,
                    'Employee_id' => $leave->employee->id,
                    'leave_type' => $leave->leavetype->name,
                    'leave_id' => $leave->leavetype->id,
                    'reason' => $leave->reason,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date
                ];
            });
        if ($pending_application->isEmpty()) {
            return response()->json([
                'status' => false,
                "message" => "No Employee has applied for leave"
            ], 404);
        }
        return response()->json([
            'status' => true,
            "message" => "Successfully fetched pending leave applications",
            "data" => $pending_application
        ], 200);
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
