<?php

namespace App\Http\Controllers\Api\Quality;

use App\Http\Controllers\Controller;
use App\Models\Ncr;
use App\Models\QaInspection;
use Illuminate\Http\Request;

class QaDashboardController extends Controller
{
    public function index()
{
    return response()->json([
        'open_ncr' => Ncr::where('status','open')->count(),
        'closed_ncr' => Ncr::where('status','closed')->count(),
        'pending_inspections' => QaInspection::where('status','submitted')->count(),
        'approved_inspections' => QaInspection::where('status','approved')->count(),
    ]);
}
}
