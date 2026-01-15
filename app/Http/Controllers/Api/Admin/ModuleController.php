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
            'data' => Module::all() 
        ]);   
     }

      public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:modules,name']);

        $module = Module::create(['name' => $request->name]);

        return response()->json([
            'status' => true,
            'role' => $module
        ]);
    }

    public function update(Request $request, $id)
    {
        $module = Module::findOrFail($id);

        $module->update([
            'is_enabled' => $request->is_enabled
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Module updated'
        ]);
    }
}