<?php

namespace App\Http\Controllers\Api\Execution;

use App\Http\Controllers\Controller;
use App\Models\BoqItemProgress;
use App\Models\Installation;
use Illuminate\Http\Request;

class InstallationController extends Controller
{
    public function store(Request $request)
{
    Installation::create($request->all());

    BoqItemProgress::where('boq_id',$request->boq_id)
        ->increment('installed_qty',$request->installed_qty);

    return response()->json(['message'=>'Installation updated']);
}

}
