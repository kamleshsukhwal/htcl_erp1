<?php

namespace App\Http\Controllers\Api\Quality;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function byModule($module)
{
    $logs = AuditLog::where('module_name', $module)->latest()->get();

    return response()->json([
        'data' => $logs
    ]);
}

public function byProject($projectId)
{
    $logs = AuditLog::where('project_id', $projectId)->latest()->get();

    return response()->json([
        'data' => $logs
    ]);
}

public function byUser($userId)
{
    $logs = AuditLog::where('user_id', $userId)->latest()->get();

    return response()->json([
        'data' => $logs
    ]);
}
}
