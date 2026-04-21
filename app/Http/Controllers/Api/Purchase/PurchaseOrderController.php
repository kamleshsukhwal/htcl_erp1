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
    $purchaseOrders = PurchaseOrder::where('status',1)->get()->map(function ($po) {
        $po->grand_total = $po->total_amount + $po->gst_amount;
        return $po;
    });

    return response()->json([
        'status' => true,
        'data' => $purchaseOrders
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
        'items.*.unit_price'  => 'required|numeric',
        'items.*.total'       => 'required|numeric',
    ]);

    DB::beginTransaction();

    try {
        // ✅ Calculate GST (18%)
        $gstAmount = $request->total_amount * 0.18;
        $grandTotal = $request->total_amount + $gstAmount;

        // 1️⃣ Create PO Header
        $po = PurchaseOrder::create([
            'vendor_id'    => $request->vendor_id,
            'po_number'    => $request->po_number,
            'project_id'   => $request->project_id,
            'order_date'   => $request->order_date,
            'total_amount' => $request->total_amount,
            'gst_amount'   => $gstAmount,
            'status'       => $request->status,
        ]);

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

        // ✅ Add grand total in response
        $po->grand_total = $grandTotal;

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
/*** enatbel log store after remaining issue resolve 24-feb kamlesh 5:21PM */

/*
AuditLog::create([
    'module_name' => 'purchase_orders',
    'record_id' => $po->id,
    'action' => 'update',
    'old_data' => json_encode($oldData),
    'new_data' => json_encode($po),
    'performed_by' => auth()->id()
]);
*/

 public function show($id)
{
    $po = PurchaseOrder::with('items.boqItem')->find($id);

    if (!$po) {
        return response()->json([
            'message' => 'Purchase Order not found'
        ], 404);
    }

    // ✅ Add grand total
    $po->grand_total = $po->total_amount + $po->gst_amount;

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

