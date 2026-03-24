<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Module::where('status',1)->get() 
        ]);   
     }

      public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|unique:modules,name',
            'status' => 'required|in:0,1' // or boolean
        ]);

        $module = Module::create([
            'name'       => $request->name,
            'status'     => $request->status,
            'created_by' => auth()->id() ?? 0,
            'updated_by' => auth()->id() ?? 0,
        ]);

        return response()->json([
            'status' => true,
            'role' => $module
        ]);
    }

    public function update(Request $request, $id)
    {
        $module = Module::findOrFail($id);

        $request->validate([
            'is_enabled' => 'required|in:0,1', // or boolean
            'status'     => 'required|in:0,1'
        ]);

        $module->update([
            'is_enabled' => $request->is_enabled,
            'status'     => $request->status,
            'updated_by' => auth()->id() ?? 0,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Module updated'
        ]);
    }
}