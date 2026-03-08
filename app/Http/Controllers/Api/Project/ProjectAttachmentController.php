<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectAttachment;
use Illuminate\Support\Facades\Auth;

class ProjectAttachmentController extends Controller
{
    // Upload file
  public function upload(Request $request,$project_id)
{
    $request->validate([
        'file' => 'required|file|max:10240'
    ]);

    $file = $request->file('file');

    $path = $file->store('project_files','public');

    $attachment = ProjectAttachment::create([
        'project_id' => $project_id,
        'file_name' => $file->getClientOriginalName(),
        'file_path' => $path,
        'file_type' => $file->getClientMimeType(),
        'uploaded_by' => Auth::id()
    ]);

    return response()->json([
        'status'=>true,
        'message'=>'File uploaded',
        'data'=>$attachment
    ]);
}

    // List project files
    public function list($project_id)
    {
        $files = ProjectAttachment::where('project_id',$project_id)->get();

        return response()->json([
            'status' => true,
            'data' => $files
        ]);
    }

    // Download file
    public function download($id)
    {
        $file = ProjectAttachment::findOrFail($id);

        return response()->download(storage_path('app/public/'.$file->file_path));
    }

    // Delete file
    public function delete($id)
    {
        ProjectAttachment::findOrFail($id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'File deleted'
        ]);
    }
}