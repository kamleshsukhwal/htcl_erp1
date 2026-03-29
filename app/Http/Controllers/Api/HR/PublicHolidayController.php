<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\PublicHoliday;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class PublicHolidayController extends Controller
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
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:public_holiday,date',
        ]);

        $publicHoliday = PublicHoliday::create($validatedData);

        return response()->json([
            'message' => 'Public holiday created successfully',
            'data' => $publicHoliday
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $name)
    {
        $publicHoliday = PublicHoliday::where('name', $name)->first();

        if (!$publicHoliday) {
            return response()->json([
                'message' => 'Public holiday not found'
            ], 404);
        }

        return response()->json([
            'data' => $publicHoliday
        ]);
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
        $publicHoliday = PublicHoliday::find($id);

        if (!$publicHoliday) {
            return response()->json([
                'message' => 'Public holiday not found'
            ], 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'date' => [
                'sometimes',
                'required',
                'date',
                Rule::unique('public_holiday', 'date')->ignore($id),
            ],
        ]);

        $publicHoliday->update($validatedData);

        return response()->json([
            'message' => 'Public holiday updated successfully',
            'data' => $publicHoliday
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $name)
    {
        $publicHoliday = PublicHoliday::where('name',$name)->first();

        if (!$publicHoliday) {
            return response()->json([
                'message' => 'Public holiday not found'
            ], 404);
        }

        $publicHoliday->delete();

        return response()->json([
            'message' => 'Public holiday deleted successfully'
        ]);
    }
}
