<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Http\Requests\StoreFeedbackRequest;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(StoreFeedbackRequest $request)
    {
        $feedback = Feedback::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Feedback submitted successfully',
            'data' => $feedback
        ]);
    }

    public function index()
    {
        $feedback = Feedback::with('user')->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $feedback
        ]);
    }

    public function filterByDate(Request $request)
    {
        $feedback = Feedback::when($request->from_date && $request->to_date, function ($query) use ($request) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        })->get();

        return response()->json([
            'status' => true,
            'data' => $feedback
        ]);
    }
}