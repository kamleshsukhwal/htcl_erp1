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
   
  public function index()
    {
        return PurchaseOrder::where('status',1)->get();
    }


     public function store(Request $request)
    {
        $request->validate([
            'vendor_id'     => 'required|integer',
            //'boq_id'  => 'required|integer',
            'po_number'     => 'required|string|unique:purchase_orders',
            'project_id'    => 'required|integer',
            'order_date'    => 'required|date',
            'total_amount'  => 'required|numeric',
            'status'        => 'required|string',
            'items'         => 'required|array|min:1',
            'items.*.item_name'   => 'required|string',
            'items.*.ordered_qty'    => 'required|numeric',
            'item.*.boq_id'=> 'required|numeric',
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
                  'boq_id' => $item['boq_id'],
                    'item_name'         => $item['item_name'],
                    'ordered_qty'          => $item['ordered_qty'],
                    'unit_price'        => $item['unit_price'],
                   // 'rate' => $item['rate'],
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


    public function show($id)
{
    $po = PurchaseOrder::with('items')->find($id);

    if (!$po) {
        return response()->json([
            'message' => 'Purchase Order not found'
        ], 404);
    }

    return response()->json([
        'message' => 'Purchase Order details',
        'data' => $po
    ], 200);
}

}


