<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    // 1️⃣ List Clients
    public function index(Request $request)
    {
        $clients = Client::when($request->search, function ($q) use ($request) {
        $q->where('name', 'like', '%' . $request->search . '%');
    })
    ->when($request->status, function ($q) use ($request) {
        $q->where('status', $request->status);
    })
    ->latest()
    ->paginate(10);
        return response()->json($clients);
    }


    // 2️⃣ Store Client
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'pancard_no' => 'nullable|string|max:20',
            'gst_no'=> 'nullable|string|max:50',
        ]);

        $client = Client::create([
    'client_code'     => $request->client_code,
    'name'            => $request->name,
    'email'           => $request->email,
    'phone'           => $request->phone,
    'contact_person'  => $request->contact_person,
    'alternate_phone' => $request->alternate_phone,
    'address'         => $request->address,
    'pancard_no'      => $request->pancard_no,
    'gst_no'          => $request->gst_no,
    'status'          => $request->status ?? 'active'
]);

        return response()->json([
            'message' => 'Client created successfully',
            'data'    => $client
        ], 201);
    }

    // 3️⃣ Show Client
    public function show($id)
    {
        $client = Client::findOrFail($id);

        return response()->json($client);
    }

    // 4️⃣ Update Client
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

       $request->validate([
        'name'  => 'sometimes|required|string|max:255',
       // 'name'  => 'sometimes|required|string|max:255',
        'email' => 'sometimes|nullable|email',
        'phone' => 'sometimes|nullable|string|max:20',
      
        'contact_person'  => 'sometimes|required|string|max:100',
        'alternate_phone'  => 'sometimes|required|string|max:255',
        'address'  => 'sometimes|required|string|max:255',
        'pancard_no' => 'sometimes|nullable|string|max:20',
        'gst_no'=> 'sometimes|nullable|string|max:50',
        'status'=> 'sometimes|required|in:active,inactive'
]);
/****  'client_code',        'name',
        'email',            'phone',
        'company_name',    'contact_person',
        'alternate_phone',        'address',
        'pancard_no',        'gst_no',        'status'
    ]; */
        $client->update($request->all());

        return response()->json([
            'message' => 'Client updated successfully',
            'data'    => $client
        ]);
    }

    // 5️⃣ Delete Client
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json([
            
        'message' => 'Client deleted successfully'
 


]);
    }
}
