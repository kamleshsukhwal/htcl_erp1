<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectDashboardController extends Controller
{
    public function projectProgress($projectId)
    {
        $total = DB::table('boq_items')
            ->join('boqs', 'boqs.id', '=', 'boq_items.boq_id')
            ->where('boqs.project_id', $projectId)
            ->sum(DB::raw('boq_items.qty * boq_items.rate'));

        $executed = DB::table('boq_item_progress')
            ->join('boq_items', 'boq_items.id', '=', 'boq_item_progress.boq_item_id')
            ->join('boqs', 'boqs.id', '=', 'boq_items.boq_id')
            ->where('boqs.project_id', $projectId)
            ->sum(DB::raw('boq_item_progress.executed_qty * boq_items.rate'));

        $percent = $total > 0 ? round(($executed / $total) * 100, 2) : 0;

        return response()->json([
            'total_value' => $total,
            'executed_value' => $executed,
            'progress_percent' => $percent
        ]);
    }
    public function boqWiseValue($projectId)
    {
        return DB::table('boqs')
            ->select('boq_name', DB::raw('SUM(total_amount) as value'))
            ->where('project_id', $projectId)
            ->groupBy('boq_name')
            ->get();
    }
}
