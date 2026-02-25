<?php

namespace App\Http\Controllers\Api\Quality;

use App\Http\Controllers\Controller;
use App\Models\Ncr;
use App\Models\QaAttachment;
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


public function upload(Request $request,$id)
{
    $file = $request->file('file');
    $path = $file->store('qa','public');

    QaAttachment::create([
        'module' => 'ncr',
        'module_id' => $id,
        'file_path' => $path
    ]);

    return response()->json(['message'=>'Uploaded']);
}

}
