<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDay;
use App\Models\AttendanceSession;
use App\Models\ProjectTeamMember;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MemberAttendanceController extends Controller
{

    // CHECK-IN
    public function checkIn(Request $request)
    { 
    $request->validate([

        'project_id' => 'required|exists:projects,id',

        'project_team_member_id' =>
            'required|exists:project_team_members,id',
    ]);

    // CHECK MEMBER
    $member = ProjectTeamMember::where(
        'id',
        $request->project_team_member_id
    )
    ->where(
        'project_id',
        $request->project_id
    )
    ->first();

    if (!$member) {

        return response()->json([
            'message' => 'Member not assigned to this project'
        ], 400);
    }

    // CHECK OPEN SESSION
    $openSession = AttendanceSession::whereHas(
        'attendanceDay',
        function ($q) use ($request) {

            $q->where(
                'project_team_member_id',
                $request->project_team_member_id
            );
        }
    )
    ->whereNull('check_out')
    ->first();

    if ($openSession) {

        return response()->json([
            'message' => 'Previous session still open'
        ], 400);
    }

    $today = date('Y-m-d');

    // CREATE DAY
    $attendanceDay = AttendanceDay::firstOrCreate(

        [
            'project_id' => $request->project_id,

            'project_team_member_id' =>
                $request->project_team_member_id,

            'attendance_date' => $today
        ],

        [
            'status' => 'present'
        ]
    );

    // CREATE SESSION
    $session = AttendanceSession::create([

        'attendance_day_id' => $attendanceDay->id,

        'check_in' => now(),

        'checkin_latitude' => $request->latitude,

        'checkin_longitude' => $request->longitude,
    ]);

    return response()->json([

        'message' => 'Check-in successful',

        'session' => $session
    ]);
}

    // CHECK-OUT
   public function checkOut(Request $request)
{
    $request->validate([

        'session_id' =>
            'required|exists:attendance_sessions,id',
    ]);

    $session = AttendanceSession::findOrFail(
        $request->session_id
    );

    if ($session->check_out) {

        return response()->json([
            'message' => 'Already checked out'
        ], 400);
    }

    $checkOut = now();

    // CALCULATE HOURS
    $hours = Carbon::parse($session->check_in)
        ->diffInMinutes($checkOut) / 60;

    // UPDATE SESSION
    $session->update([

        'check_out' => $checkOut,

        'checkout_latitude' => $request->latitude,

        'checkout_longitude' => $request->longitude,

        'worked_hours' => round($hours, 2),
    ]);

    // TOTAL HOURS
    $total = AttendanceSession::where(
        'attendance_day_id',
        $session->attendance_day_id
    )->sum('worked_hours');

    AttendanceDay::where(
        'id',
        $session->attendance_day_id
    )->update([

        'total_hours' => round($total, 2)
    ]);

    return response()->json([

        'message' => 'Checkout successful',

        'worked_hours' => round($hours, 2),

        'total_hours' => round($total, 2)
    ]);
}

    // HISTORY
    public function history($projectId)
    {
        $history = AttendanceDay::with([
            'member',
            'sessions'
        ])
        ->where('project_id', $projectId)

        ->orderBy('attendance_date', 'desc')

        ->get();

        return response()->json([
            'data' => $history
        ]);
    }



    public function memberHistory($memberId)
{
    $history = AttendanceDay::with([
        'member',
        'sessions'
    ])
    ->where(
        'project_team_member_id',
        $memberId
    )
    ->orderBy('attendance_date', 'desc')
    ->get();

    return response()->json([
        'data' => $history
    ]);
}

public function todayAttendance($projectId)
{
    $today = date('Y-m-d');

    $attendance = AttendanceDay::with([
        'member',
        'sessions'
    ])
    ->where('project_id', $projectId)

    ->where('attendance_date', $today)

    ->get();

    return response()->json([
        'data' => $attendance
    ]);
}

public function deleteSession($id)
{
    $session = AttendanceSession::findOrFail($id);

    $attendanceDayId = $session->attendance_day_id;

    $session->delete();

    // RECALCULATE TOTAL
    $total = AttendanceSession::where(
        'attendance_day_id',
        $attendanceDayId
    )->sum('worked_hours');

    AttendanceDay::where(
        'id',
        $attendanceDayId
    )->update([
        'total_hours' => $total
    ]);

    return response()->json([
        'message' => 'Session deleted successfully'
    ]);
}


public function attendanceReport(Request $request)
{
    $query = AttendanceDay::with([
        'member',
        'sessions'
    ]);

    // PROJECT FILTER
    if ($request->project_id) {

        $query->where(
            'project_id',
            $request->project_id
        );
    }

    // MEMBER FILTER
    if ($request->project_team_member_id) {

        $query->where(
            'project_team_member_id',
            $request->project_team_member_id
        );
    }

    // SINGLE DATE FILTER
    if ($request->attendance_date) {

        $query->where(
            'attendance_date',
            $request->attendance_date
        );
    }

    // DATE RANGE FILTER
    if (
        $request->from_date &&
        $request->to_date
    ) {

        $query->whereBetween(
            'attendance_date',
            [
                $request->from_date,
                $request->to_date
            ]
        );
    }

    // STATUS FILTER
    if ($request->status) {

        $query->where(
            'status',
            $request->status
        );
    }

    $data = $query
        ->orderBy('attendance_date', 'desc')
        ->get();

    return response()->json([
        'data' => $data
    ]);
}

}