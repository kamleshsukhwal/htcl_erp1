<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ProjectTeamMember;
use App\Models\User;

class ProjectTeamController extends Controller
{

    // ADD MEMBER
    
public function addMember(Request $request)
{
    $request->validate([

        'project_id' => 'required|exists:projects,id',

        'member_name' => 'required|string|max:255',

        'mobile' => 'nullable|string|max:20',

        'email' => 'nullable|email',

        'designation' => 'nullable|string|max:255',

        'employee_code' => 'nullable|string|max:100',
    ]);

    // CHECK DUPLICATE MEMBER IN SAME PROJECT
    $exists = ProjectTeamMember::where(
        'project_id',
        $request->project_id
    )
    ->where(
        'member_name',
        $request->member_name
    )
    ->exists();

    if ($exists) {

        return response()->json([
            'message' => 'Member already added in this project'
        ], 400);
    }

    // CREATE MEMBER
    $member = ProjectTeamMember::create([

        'project_id' => $request->project_id,

        'member_name' => $request->member_name,

        'mobile' => $request->mobile,

        'email' => $request->email,

        'designation' => $request->designation,

        'employee_code' => $request->employee_code,

        'can_login' => $request->can_login ?? 0,

        'email_notification' =>
            $request->email_notification ?? 1,

        'sms_notification' =>
            $request->sms_notification ?? 0,
    ]);

    return response()->json([
        'message' => 'Team member added successfully',
        'data' => $member
    ]);


    }

    // LIST MEMBERS
    public function members($projectId)
{
    $members = ProjectTeamMember::where(
        'project_id',
        $projectId
    )->get();

    return response()->json([
        'data' => $members
    ]);
}

    // REMOVE MEMBER
   // public function removeMember($id)
     public function removeMember($id)
{
    $member = ProjectTeamMember::findOrFail($id);

    $member->delete();

    return response()->json([
        'message' => 'Member removed successfully'
    ]);
}

    // ENABLE/DISABLE NOTIFICATION
    public function updateNotification(
        Request $request,
        $id
    ) {

        $request->validate([
            'email_notification' => 'required|boolean',
            'sms_notification' => 'nullable|boolean',
        ]);

        $member = ProjectTeamMember::findOrFail($id);

        $member->update([

            'email_notification' =>
                $request->email_notification,

            'sms_notification' =>
                $request->sms_notification ?? 0,
        ]);

        return response()->json([
            'message' => 'Notification updated successfully'
        ]);
    }
}