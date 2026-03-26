<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

  class VendorController extends Controller
{
  
public function store(Request $request)
{
    // 1️⃣ Validate
   // dd($request);
   //dd($request->all());
   $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'nullable|email|max:255',
    'gst_number' => 'nullable|string|max:255',
    'vendor_code' => 'nullable|string|max:100',
    'address' => 'nullable|string|max:500',
    'pancard' => 'nullable|string|max:50'
    
]);

    // 2️⃣ Create Vendor
    $vendor = Vendor::create([
        'name' => $request->name,
        'email' => $request->email,
        'gst_number' => $request->gst_number,
        'vendor_code' => $request->vendor_code,
        'address' => $request->address,
        'pancard' => $request->pancard,
        'status' => 'active'
    ]);

    // 3️⃣ Upload Attachments
     
    return response()->json([
        'status' => true,
        'message' => 'Vendor added successfully',
        'data' => $vendor
    ], 201);
}

    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Vendor::latest()->paginate(10)
        ]);
    }

  public function update(Request $request, $id)
{
    $vendor = \App\Models\Vendor::find($id);

    if (!$vendor) {
        return response()->json([
            'status' => false,
            'message' => 'Vendor not found'
        ], 404);
    }

    // Validate
    $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|nullable|email|max:255',
        'gst_number' => 'nullable|string|max:255',
        'vendor_code' => 'nullable|string|max:100',
        'address' => 'nullable|string|max:500',
        'pancard' => 'nullable|string|max:50',
        'status' => 'nullable|string',
        'attachments.*' => 'nullable|file|max:20048'
    ]);

    // Update Vendor
    $vendor->update([
        'name' => $request->name ?? $vendor->name,
        'email' => $request->email ?? $vendor->email,
        'gst_number' => $request->gst_number ?? $vendor->gst_number,
        'vendor_code' => $request->vendr_code ?? $vendor->vendr_code,
        'address' => $request->address ?? $vendor->address,
        'pancard' => $request->pancard ?? $vendor->pancard,
        'status' => $request->status ?? $vendor->status
    ]);

    // Upload new documents if provided
     

    return response()->json([
        'status' => true,
        'message' => 'Vendor updated successfully',
        'data' => $vendor
    ]);
}


    public function show($id)
{
    $vendor = \App\Models\Vendor::find($id);

    if (!$vendor) {
        return response()->json([
            'status' => false,
            'message' => 'Vendor not found'
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'Vendor details',
        'data' => $vendor
    ]);
}

public function destroy($id)
{
    $vendor = \App\Models\Vendor::find($id);

    if (!$vendor) {
        return response()->json([
            'status' => false,
            'message' => 'Vendor not found'
        ], 404);
    }

    $vendor->delete();

    return response()->json([
        'status' => true,
        'message' => 'Vendor deleted successfully'
    ]);
}


/***** upload document */
public function uploadDocument(Request $request, $vendorId)
{
    $request->validate([
        'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
    ]);

    $vendor = Vendor::find($vendorId);

    if (!$vendor) {
        return response()->json([
            'status' => false,
            'message' => 'Vendor not found'
        ], 404);
    }

    $file = $request->file('file');

    $fileName = uniqid().'_'.$file->getClientOriginalName();

    // store in storage/app/private/vendors
    $file->storeAs('vendors', $fileName,'private');

    $attachment = VendorAttachment::create([
        'vendor_id' => $vendorId,
        'file_name' => $fileName,
        'original_name' => $file->getClientOriginalName(),
        'file_path' => 'vendors/'.$fileName,
        'mime_type' => $file->getMimeType(),
        'file_size' => $file->getSize()
    ]);

    return response()->json([
        'status' => true,
        'message' => 'File uploaded successfully',
        'data' => $attachment
    ]);
}

/**** fetch all document related to vendor id */

public function getVendorDocuments($vendorId)
{
    $vendor = Vendor::with('attachments')->find($vendorId);

    if (!$vendor) {
        return response()->json([
            'status' => false,
            'message' => 'Vendor not found'
        ], 404);
    }

    return response()->json([
        'status' => true,
        'vendor_id' => $vendor->id,
        'documents' => $vendor->attachments
    ]);
}


/**** download document */

 
public function downloadDocument($id)
{
    $attachment = VendorAttachment::find($id);

    if (!$attachment) {
        return response()->json([
            'status' => false,
            'message' => 'File not found'
        ], 404);
    }

    $path = $attachment->file_path;

    if (!Storage::disk('private')->exists($path)) {
        return response()->json([
            'status' => false,
            'message' => 'File missing on server'
        ], 404);
    }

    return Storage::disk('private')->download($path, $attachment->original_name);
}


/*** delete attachement */

public function deleteDocument($id)
{
    $attachment = VendorAttachment::find($id);

    if (!$attachment) {
        return response()->json([
            'status' => false,
            'message' => 'Document not found'
        ], 404);
    }

    $path = $attachment->file_path;

    if (Storage::disk('private')->exists($path)) {
        Storage::disk('private')->delete($path);
    }

    $attachment->delete();

    return response()->json([
        'status' => true,
        'message' => 'Document deleted successfully'
    ]);
}
     public function viewFile($id)
    {
        $file =VendorAttachment::where('vendor_id' , $id)->orderBy('id','desc')->first();

        $path = Storage::disk('private')->path($file->file_path);

        if (!file_exists($path)) {
            return response()->json([
                'status' => false,
                'message' => 'File not found'
            ], 404);
        }

        return response()->file($path);
    }

    // ✅ Download file
    public function downloadFile($id)
    {
        $file = VendorAttachment::where('vendor_id' , $id)->orderBy('id','desc')->first();

        if (!Storage::disk('private')->exists($file->file_path)) {
            return response()->json([
                'status' => false,
                'message' => 'File not found'
            ], 404);
        }

        return Storage::disk('private')->download(
            $file->file_path,
            $file->file_name
        );
    }
}