<?php

namespace App\Http\Controllers\Api\HR;
use App\Http\Controllers\Controller;
use App\Models\LetterFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LetterFormatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'message_type' => 'required|string|max:100',
            'message' => 'required|string|max:500',
        ]);

        $letterFormat = LetterFormat::create([
            'message_type' => $data['message_type'],
            'message' => $data['message'],
            'added_time' => now(),
            'added_by' => Auth::id(),
            'updated_on' => null,
            'updated_by' => null,
        ]);

        return response()->json([
            'status' => true,
            'data' => $letterFormat,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $letterFormat = LetterFormat::find($id);
        if (!$letterFormat) {
            return response()->json([
                'status' => false,
                'message' => 'Letter format not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $letterFormat,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'message_type' => 'required|string|max:100',
            'message' => 'required|string|max:500',
        ]);

        $letterFormat = LetterFormat::find($id);
        if (!$letterFormat) {
            return response()->json([
                'status' => false,
                'message' => 'Letter format not found'
            ], 404);
        }

        $letterFormat->update([
            'message_type' => $data['message_type'],
            'message' => $data['message'],
            'updated_on' => now(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'data' => $letterFormat,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $letterFormat = LetterFormat::find($id);
        if (!$letterFormat) {
            return response()->json([
                'status' => false,
                'message' => 'Letter format not found'
            ], 404);
        }
        $letterFormat->delete();
        return response()->json([
            'status' => true,
            'message' => 'Letter format deleted successfully'
        ], 200);
    }
}
