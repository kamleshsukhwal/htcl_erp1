<?php

namespace App\Http\Controllers\Api\Quality;

use App\Http\Controllers\Controller;
use App\Models\QaInspection;
use Illuminate\Http\Request;

class QaInspectionController extends Controller
{
   public function store(Request $request)
{
    $inspection = QaInspection::create($request->all());

    return response()->json([
        'message' => 'Inspection Created',
        'data' => $inspection
    ]);
}
}
