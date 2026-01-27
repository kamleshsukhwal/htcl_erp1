<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

  class VendorController extends Controller
{
    public function store(Request $request)
{
    // 1️⃣ Validate
    $request->validate([
        'name'    => 'required|string|max:255',
        'email'   => 'nullable|email|max:255',
        'address' => 'nullable|string|max:500',
       'gst_no' => 'nullable|string|max:500',
    ]);

    // 2️⃣ Create vendor
    $vendor = Vendor::create([
        'name'    => $request->name,
        'email'   => $request->email,
        'gst_no'  => $request->gst_no,
        'address' => $request->address,
        'status'  => 'active', // default value if needed
    ]);

    // 3️⃣ Return structured response
    return response()->json([
        'message' => 'Vendor added successfully',
        'data'    => $vendor
    ], 201);
}

    public function index()
    {
        return Vendor::where('status',1)->get();
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

    $request->validate([
        'name' => 'sometimes|required|string',
        'email' => 'sometimes|required|email|unique:vendors,email,' . $id,
        'address' => 'nullable|string',
        'status' => 'sometimes|required|integer'
    ]);

    $vendor->update($request->all());

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


}

