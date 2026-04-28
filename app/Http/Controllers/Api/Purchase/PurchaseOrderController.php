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
    $data = PurchaseOrder::withCount('items')
        ->get();

    return response()->json([
        'status' => true,
        'data' => $data
    ]);
}


     public function store(Request $request)
    { $request->validate([
    'vendor_id'     => 'required|integer',
    'po_number'     => 'required|string|unique:purchase_orders',
    'project_id'    => 'required|integer',
    'order_date'    => 'required|date',
    'client_id'     => 'nullable|integer',
    'delivery_date' => 'nullable|date',
    'approved_by'   => 'nullable|integer',
    'approved_status' => 'nullable|string|in:pending,approved,rejected',

    'devlivery_address' => 'nullable|string',
    'total_amount'  => 'required|numeric',
    'gst_amount'    => 'nullable|numeric',
    't_c'           => 'nullable|string',
    'notes'         => 'nullable|string',
    'deliver_to'    => 'nullable|string',
    'status'        => 'required|string',

    'items'                     => 'required|array|min:1',
    'items.*.item_name'         => 'required|string',
    'items.*.ordered_qty'       => 'required|numeric',
    'items.*.unit_price'        => 'required|numeric',
    'items.*.total'             => 'required|numeric',
    'items.*.is_manual'         => 'nullable|boolean',
    'items.*.boq_item_id'       => 'nullable|numeric',
    'items.*.is_billable'       => 'nullable|boolean', // ✅ NEW
]);

DB::beginTransaction();

try {
    // ✅ Calculate base total
    $calculatedBaseTotal = 0;

    foreach ($request->items as $item) {
        $calculatedBaseTotal += ($item['ordered_qty'] * $item['unit_price']);
    }

    // ✅ GST calculation
    $gstAmount = $request->gst_amount ?? ($calculatedBaseTotal * 0.18);

    // ✅ Final total
    $finalTotal = $calculatedBaseTotal + $gstAmount;

    // 1️⃣ Create PO Header
    $po = PurchaseOrder::create([
        'vendor_id'     => $request->vendor_id,
        'po_number'     => $request->po_number,
        'project_id'    => $request->project_id,
        'order_date'    => $request->order_date,

        // ✅ NEW FIELDS
        'delivery_date'   => $request->delivery_date,
        'approved_by'     => $request->approved_by,
        'approved_status' => $request->approved_status ?? 'pending',

        'total_amount'  => $finalTotal,
        'gst_amount'    => $gstAmount,
        'deliver_to'    => $request->deliver_to,
        'status'        => $request->status,
        't_c'           => $request->t_c,
        'notes'         => $request->notes,
    ]);

    // 2️⃣ Create PO Items
    foreach ($request->items as $item) {

        $base = $item['ordered_qty'] * $item['unit_price'];

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'boq_item_id'       => $item['boq_item_id'] ?? null,
            'item_name'         => $item['item_name'],
            'ordered_qty'       => $item['ordered_qty'],
            'unit_price'        => $item['unit_price'],
            'total'             => $base,
            'is_manual'         => $item['is_manual'] ?? 0,

            // ✅ NEW FIELD
            'is_billable'       => $item['is_billable'] ?? 1,
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

    return response()->json([
        'message' => 'Purchase Order details',
        'data' => $po
    ], 200);
}

public function approvebyadmin($id)
{
     DB::beginTransaction();

    try {
        $po = PurchaseOrder::findOrFail($id);

        // ❌ Prevent re-approval
        if ($po->approved_status === 'approved') {
            return response()->json([
                'message' => 'PO already approved'
            ], 400);
        }

        // ✅ Update approval
        $po->update([
            'approved_status' => 'approved',
            'approved_by'     => auth()->id(), // logged-in admin
        ]);

        DB::commit();

        return response()->json([
            'message' => 'PO approved successfully',
            'data'    => $po
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Error approving PO',
            'error'   => $e->getMessage()
        ], 500);
    }
}

}