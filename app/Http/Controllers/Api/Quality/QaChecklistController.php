<?php

namespace App\Http\Controllers\Api\Quality;

use App\Http\Controllers\Controller;
use App\Models\QaChecklist;
use Illuminate\Http\Request;

class QaChecklistController extends Controller
{
   public function store(Request $request)
{
    $checklist = QaChecklist::create($request->all());

    return response()->json([
        'message' => 'Checklist Added',
        'data' => $checklist
    ]);
}
}
