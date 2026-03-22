<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log an audit entry
     *
     * @param string $module      Module name (NCR, PO, Project, etc)
     * @param int|string $recordId Record affected
     * @param string $action       create|update|delete
     * @param array|null $oldData  Old values
     * @param array|null $newData  New values
     * @param string|null $user    Username, defaults to Auth user
     */
    public static function log($module, $recordId, $action, $oldData = null, $newData = null, $userId  = null)
{
    if (!$module) {
        throw new \Exception("AuditService: module_name is required");
    }

    if (!$recordId) {
        throw new \Exception("AuditService: record_id is required");
    }

    if (!$userId && \Auth::check()) {
        $userId = \Auth::id();
    }

    return AuditLog::create([
        'module_name'  => $module,
        'record_id'    => $recordId,
        'action'       => $action,
        'old_data'     => $oldData ? json_encode($oldData) : null,
        'new_data'     => $newData ? json_encode($newData) : null,
        'performed_by' => $userId,
    ]);
}
}