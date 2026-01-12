<?php

namespace App\Http\Controllers\Api\Boq;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\BoqFile;

class BoqController extends Controller
{

 // ✅ ADD THIS METHOD
    public function listByProject(Request $request, $projectId)
    {
        $query = Boq::where('project_id', $projectId);

        if ($request->discipline) {
            $query->where('discipline', $request->discipline);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->with_count) {
            $query->withCount('items');
        }

        if ($request->with_total) {
            $query->withSum('items', 'total_amount');
        }

        $boqs = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $boqs
        ]);
    }
    // CREATE BOQ HEADER
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'boq_name' => 'required',
            'discipline' => 'required'
        ]);

        $boq = Boq::create([
            'project_id' => $request->project_id,
            'boq_name' => $request->boq_name,
            'discipline' => $request->discipline,
            'status' => 'draft',
            'created_by' => auth()->id()
        ]);

        return response()->json(['status' => true, 'data' => $boq]);
    }

    // 👉 COPY THIS: ADD BOQ ITEMS
    public function addItems(Request $request, $boqId)
    {
        $request->validate([
            'items' => 'required|array'
        ]);

        foreach ($request->items as $row) {
            BoqItem::create([
                'boq_id' => $boqId,
                'sn' => $row['sn'] ?? null,
                'description' => $row['description'],
                'unit' => $row['unit'] ?? null,
                'quantity' => $row['quantity'] ?? 0,
                'rate' => $row['rate'] ?? 0,
                'total_amount' => ($row['quantity'] ?? 0) * ($row['rate'] ?? 0),
                'scope' => $row['scope'] ?? null,
                'approved_make' => $row['approved_make'] ?? null,
                'offered_make' => $row['offered_make'] ?? null,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'BOQ items added successfully'
        ]);
    }

    // 👉 COPY THIS: UPLOAD FILE
    public function uploadFile(Request $request, $boqId)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,pdf|max:10240'
        ]);

        $file = $request->file('file');
        $fileName = time().'_'.$file->getClientOriginalName();

        $path = $file->storeAs(
            'boqs/'.$boqId,
            $fileName,
            'public'
        );

        BoqFile::create([
            'boq_id' => $boqId,
            'file_name' => $fileName,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'uploaded_by' => auth()->id()
        ]);

        return response()->json([
            'status' => true,
            'message' => 'File uploaded successfully',
            'path' => $path
        ]);
    }
}
