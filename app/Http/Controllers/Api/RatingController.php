<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Http\Requests\StoreRatingRequest;
use Illuminate\Http\Request;


class RatingController extends Controller
{
    public function store(StoreRatingRequest $request)
    {
        $rating = Rating::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Rating created successfully',
            'data' => $rating
        ]);
    }

    public function index()
    {
        $ratings = Rating::with('user')->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $ratings
        ]);
    }

    public function getByEmployee($id)
    {
        $ratings = Rating::where('employee_id', $id)->get();

        return response()->json([
            'status' => true,
            'data' => $ratings
        ]);
    }

    public function average()
    {
        $average = Rating::avg('rating') ?? 0;

        return response()->json([
            'status' => true,
            'average_rating' => round($average, 2)
        ]);
    }
}