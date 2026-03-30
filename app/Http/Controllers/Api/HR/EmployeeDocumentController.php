<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\employee_document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EmployeeDocumentController extends Controller
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
    public function store(Request $request, $employee_id)
    {
        $mandatoryFiles = ['resume', 'aadhar_card'];

        $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'file|mimes:pdf,jpeg,png,jpg,doc,docx|max:2048'
        ]);


        $files = $request->file('documents');

        if (!$files) {
            return response()->json([
                'status' => false,
                'message' => 'No documents received'
            ], 400);
        }

        $uploadedDocuments = array_keys($files);

        $missingFiles = array_diff($mandatoryFiles, $uploadedDocuments);

        if (!empty($missingFiles)) {
            return response()->json([
                'status' => false,
                'message' => 'Missing mandatory documents: ' . implode(', ', $missingFiles)
            ], 400);
        }

        foreach ($request->file('documents') as $type => $file) {
            //Checking whether the document type already exist for the employee or not
            $exist = employee_document::where('employee_id', $employee_id)->where('document_type', $type)->exists();
            if ($exist) {
                return response()->json([
                    'status' => false,
                    'message' => "Document of type $type already exists for this employee"
                ], 400);
            }

            $document_name = $type . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs("Employee_documents/$employee_id", $document_name, 'public');

            employee_document::create([
                'employee_id' => $employee_id,
                'document_name' => $document_name,
                'document_path' => $path,
                'document_type' => $type,
                'uploaded_by' => Auth::id()
            ]);
        }
        return response()->json([
            'status' => true,
            'message' => 'Documents uploaded successfully'
        ], 201);
    }

    /**
     * 
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        // allow caller to control page size via ?per_page=
        $perPage = $request->query('per_page', 4);

        $documents = employee_document::where('employee_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        if ($documents->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No documents found for this employee'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $documents
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
        DB::beginTransaction();
        try {
            $request->validate([
                'documents' => 'required|array',
                'documents.*' => 'required|file|mimes:pdf,jpeg,png,jpg,doc,docx|max:2048'
            ]);
            $files = $request->file('documents');


            foreach ($request->file('documents') as $type => $file) {
                $exist = employee_document::where('employee_id', $id)
                    ->where('document_type', $type)->first();

                if (!$exist) {
                    throw new \Exception("Document of type $type does not exist for this employee");
                }
                $document_name = $type . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs("Employee_documents/$id", $document_name, 'public');
                $oldpath = $exist->document_path;
                $exist->update([
                    'employee_id' => $id,
                    'document_name' => $document_name,
                    'document_path' => $path,
                    'document_type' => $type,
                    'uploaded_by' => Auth::id()
                ]);
                Storage::disk('public')->delete($oldpath);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "All the documents updated successfully"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'mes' => 'Hi',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $documents = employee_document::where('employee_id', $id)->get();
        if ($documents->isEmpty()) { {
                return response()->json([
                    'status' => false,
                    'message' => "No documents found for this employee"
                ], 404);
            }
        }
        foreach ($documents as $document) {
            Storage::disk('public')->delete($document->document_path);
            employee_document::where('employee_id', $id)->where('document_type', $document->document_type)->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'All documents deleted successfully for this employee '
        ]);
    }
}
