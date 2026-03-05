<?php

namespace App\Http\Controllers\Api\Quality;

use App\Http\Controllers\Controller;
use App\Models\QaChecklist;
use Illuminate\Http\Request;

use App\Models\QaChecklistItem;

class QaChecklistController extends Controller
{
   public function store(Request $request)
{
    $checklist = QaChecklist::create($request->all());

    return response()->json([
        'message' => 'Checklist Added',
        'data' => $checklist
    ]);
}

public function show(QaChecklist $checklist)
{
    // Load checklist with items
    $checklist->load('items');

    return response()->json([
        'message' => 'Checklist details fetched successfully',
        'data' => $checklist
    ]);
}
public function index(){

    return QaChecklist::all();
}

public function addItem(Request $request, QaChecklist $checklist)
{
    // Validate input
    $validated = $request->validate([
        'check_point'  => 'required|string|max:255',
        'type'         => 'required|in:pass_fail,number,text',
        'is_required'  => 'nullable|boolean',
    ]);

    // Create checklist item
    $item = $checklist->items()->create([
        'check_point' => $validated['check_point'],
        'type'        => $validated['type'],
        'is_required' => $validated['is_required'] ?? true,
    ]);

    return response()->json([
        'message' => 'Checklist item added successfully',
        'data'    => $item
    ], 201);
}
public function destroy($id)
{
    try {
        $checklist = QaChecklist::findOrFail($id);

        $checklist->delete();

        return response()->json([
            'status' => true,
            'message' => 'Checklist deleted successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to delete checklist'
        ], 500);
    }
}
}