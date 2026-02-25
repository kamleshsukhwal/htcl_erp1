<?php

namespace App\Http\Controllers\Api\Quality;

use App\Http\Controllers\Controller;
use App\Models\Ncr;
use Illuminate\Http\Request;

class NcrController extends Controller
{
   public function store(Request $request)
{
    $ncr = Ncr::create($request->all());

    return response()->json([
        'message' => 'NCR Created',
        'data' => $ncr
    ]);
}
}
