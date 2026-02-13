<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // 🟢 BOQ Summary
    public function boqSummary($projectId)
    {
        return DB::table('boq_items')
            ->join('boqs', 'boqs.id', '=', 'boq_items.boq_id')
            ->where('boqs.project_id', $projectId)
            ->select(
                DB::raw('SUM(boq_items.quantity) as total_qty'),
                DB::raw('SUM(boq_items.quantity * boq_items.rate) as total_value')
            )
            ->first();
    }

    // 🟢 Stock Summary
    public function stockSummary($projectId)
    {
        return DB::table('dc_in_items')
            ->join('boq_items', 'boq_items.id', '=', 'dc_in_items.boq_item_id')
            ->join('boqs', 'boqs.id', '=', 'boq_items.boq_id')
            ->where('boqs.project_id', $projectId)
            ->select(
                DB::raw('SUM(dc_in_items.supplied_qty) as total_in')
            )
            ->first();
    }

    // 🟢 PO Summary
    public function poSummary($projectId)
    {
        return DB::table('purchase_orders')
            ->where('project_id', $projectId)
            ->select(
                DB::raw('SUM(total_amount) as total_po_value')
            )
            ->first();
    }
}
