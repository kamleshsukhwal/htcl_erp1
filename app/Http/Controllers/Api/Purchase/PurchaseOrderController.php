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
    $pos = PurchaseOrder::where('status',1)->get()->map(function ($po) {

        $po->total_amount = number_format(
            ((float)$po->total_amount + (float)$po->gst_amount),
            2,
            '.',
            ''
        );

        return $po;
    });

    return response()->json([
        'status' => true,
        'data' => $pos
    ]);
}


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
            'items.*.ordered_qty' => 'required|numeric',
            'items.*.is_manual'   => 'nullable|boolean',
            'items.*.boq_id'      => 'nullable|numeric',
            'items.*.boq_item_id'=> 'nullable|numeric',
            'items.*.unit_price'  => 'required|numeric',
            'items.*.total'       => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Create PO Header (NO CHANGE)
            $po = PurchaseOrder::create(
                $request->except('items')
            );

            // 2️⃣ Create PO Items
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'boq_item_id'       => $item['boq_item_id'] ?? null,
                    'item_name'         => $item['item_name'],
                    'ordered_qty'       => $item['ordered_qty'],
                    'unit_price'        => $item['unit_price'],
                    'total'             => $item['total'],
                    'is_manual'         => $item['is_manual'] ?? 0,
                ]);
            }

          DB::commit();

// override total_amount in response only
$po->total_amount = number_format(
    ((float)$po->total_amount + (float)$po->gst_amount),
    2,
    '.',
    ''
);
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
    $po = PurchaseOrder::with('items.boqItem')->find($id);

    if (!$po) {
        return response()->json([
            'message' => 'Purchase Order not found'
        ], 404);
    }

    // override total_amount
    $po->total_amount = round($po->total_amount + $po->gst_amount, 2);

    return response()->json([
        'message' => 'Purchase Order details',
        'data' => $po
    ], 200);
}

    public function approve($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'pending') {
            return response()->json(['error' => 'Already processed']);
        }

        $po->update(['status' => 'approved']);

        return response()->json(['message' => 'PO Approved']);
    }
}