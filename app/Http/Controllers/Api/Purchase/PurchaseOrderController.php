<?php

namespace App\Http\Controllers\Api\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Boq;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

   class PurchaseOrderController extends Controller
{
   
     public function store(Request $request)
    {
        $request->validate([
            'vendor_id'     => 'required|integer',
            'po_number'     => 'required|string|unique:purchase_orders',
            'project_id'    => 'required|integer',
            'order_date'    => 'required|date',
            'total_amount'  => 'required|numeric',
            'status'        => 'required|string',
            'items'         => 'required|array|min:1',
            'items.*.item_name'   => 'required|string',
            'items.*.quantity'    => 'required|numeric',
            'items.*.unit_price'  => 'required|numeric',
            'items.*.total'       => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Create PO Header
            $po = PurchaseOrder::create(
                $request->except('items')
            );

            // 2️⃣ Create PO Items
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_name'         => $item['item_name'],
                    'quantity'          => $item['quantity'],
                    'unit_price'        => $item['unit_price'],
                    'total'             => $item['total'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Purchase Order created successfully',
                'data'    => $po->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating Purchase Order',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}


