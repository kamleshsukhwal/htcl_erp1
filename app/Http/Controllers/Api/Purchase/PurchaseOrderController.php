<?php

namespace App\Http\Controllers\Api\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Traits\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;


   class PurchaseOrderController extends Controller
{
   use SendEmail;
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
{
    // ✅ Validation
    $request->validate([
        'vendor_id'     => 'required|integer',
        'po_number'     => 'required|string|unique:purchase_orders',
        'project_id'    => 'required|integer',
        'order_date'    => 'required|date',
        'client_id'     => 'nullable|integer',
        'delivery_date' => 'nullable|date',
        'delivery_address' => 'nullable|string',
        'total_amount'  => 'required|numeric',
        'gst_amount'    => 'nullable|numeric',
        't_c'           => 'nullable|string',
        'notes'         => 'nullable|string',
        'deliver_to'    => 'nullable|string',
        'status'        => 'required|in:draft,pending,pending_approval,approved,rejected,partially_received,fully_received,closed,cancelled',

        'items'                     => 'required|array|min:1',
        'items.*.item_name'         => 'required|string',
        'items.*.ordered_qty'       => 'required|numeric|min:0.01',
        'items.*.unit_price'        => 'required|numeric|min:0',
        'items.*.boq_item_id'       => 'nullable|numeric',
        'items.*.is_manual'         => 'nullable|boolean',
        'items.*.is_billable'       => 'nullable|boolean',
        'items.*.save_to_master'    => 'nullable|boolean', // ✅ NEW
    ]);

    DB::beginTransaction();
//
    try {

        // ✅ Calculate total
        $calculatedBaseTotal = 0;

        foreach ($request->items as $item) {
            $calculatedBaseTotal += ($item['ordered_qty'] * $item['unit_price']);
        }

       $gstAmount = $request->gst_amount ?? ($calculatedBaseTotal * 0.18);
        $finalTotal = $calculatedBaseTotal;

        // ✅ Create PO
        $po = PurchaseOrder::create([
            'vendor_id'     => $request->vendor_id,
            'po_number'     => $request->po_number,
            'project_id'    => $request->project_id,
            'order_date'    => $request->order_date,
            'delivery_date' => $request->delivery_date,

            'approved_by'     => null,
            'approved_status' => 'pending',

            'total_amount'  => $finalTotal,
            'gst_amount'    => $gstAmount,
            'deliver_to'    => $request->deliver_to,
            'status'        => 'draft',
            't_c'           => $request->t_c,
            'notes'         => $request->notes,
        ]);

        // =========================
        // ✅ Insert Items + BOQ logic
        // =========================
 
     foreach ($request->items as $item) {

    $boqItemId = null;

    // ✅ Case 1: Already BOQ item (NOT manual)
    if (empty($item['is_manual']) || $item['is_manual'] == 0) {

        $boqItemId = $item['boq_item_id'] ?? null;

    } else {

        // ✅ Case 2: Manual item → check & insert

        $itemName = strtolower(trim(preg_replace('/\s+/', ' ', $item['item_name'])));

        $existingBoq = BoqItem::whereRaw('LOWER(TRIM(item_name)) = ?', [$itemName])
            ->where('boq_id', 1) 
            ->first();

        if ($existingBoq) {

            $boqItemId = $existingBoq->id;

        } else {

            $boq = BoqItem::create([
                'boq_id'       => 1,
                'item_name'    => $item['item_name'],
                'description'  => $item['item_name'],
                'unit'         => 'Nos',
                'quantity'     => $item['ordered_qty'],
                'rate'         => $item['unit_price'],
                'total_amount' => $item['ordered_qty'] * $item['unit_price'],
            ]);

            $boqItemId = $boq->id;
        }
    }

    // ✅ Insert PO item
    $base = $item['ordered_qty'] * $item['unit_price'];

    PurchaseOrderItem::create([
        'purchase_order_id' => $po->id,
        'boq_item_id'       => $boqItemId,
        'item_name'         => $item['item_name'],
        'ordered_qty'       => $item['ordered_qty'],
        'unit_price'        => $item['unit_price'],
        'total'             => $base,
        'is_manual'         => $item['is_manual'] ?? 0,
        'is_billable'       => $item['is_billable'] ?? 1,
    ]);
}
        DB::commit();

        // =========================
        // ✅ SEND EMAIL AFTER COMMIT
        // =========================

       

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
/*
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
} */


/* create for email PO arroval but not working

 public function approve(Request $request, $id)
{
    $po = PurchaseOrder::findOrFail($id);

    if ($po->approved_status === 'approved') {
        return response("<h3>PO already approved</h3>");
    }

    $po->update([
        'approved_status' => 'approved',
        'approved_by' => $request->user_id,
        'approved_at' => now()
    ]);

    return response("<h2>✅ PO Approved Successfully</h2>");
}*/



/*** PO summary for boxes */


public function poSummary(Request $request)
{  // Base query
    $query = DB::table('purchase_orders');

    // Optional filters
    if ($request->project_id) {
        $query->where('project_id', $request->project_id);
    }

    if ($request->from_date && $request->to_date) {
        $query->whereBetween('order_date', [$request->from_date, $request->to_date]);
    }

    // ✅ Total PO Amount
    $total_po = (clone $query)->sum('total_amount');

    // ✅ Approved Amount
    $approved_po = (clone $query)
        ->where('approved_status', 'approved')
        ->sum('total_amount');

    // ✅ Pending Amount
    $pending_po = (clone $query)
        ->where('approved_status', 'pending')
        ->sum('total_amount');

    // ===============================
    // ✅ Received Amount (CORRECT LOGIC)
    // ===============================

    $received_amount = DB::table('dc_in_items as dci')
        ->join('dc_ins as dcih', 'dcih.id', '=', 'dci.dc_in_id')
        ->join('purchase_orders as po', 'po.id', '=', 'dcih.purchase_order_id')
        ->leftJoin('purchase_order_items as poi', function ($join) {
            $join->on('poi.purchase_order_id', '=', 'po.id')
                 ->on(DB::raw('LOWER(TRIM(poi.item_name))'), '=', DB::raw('LOWER(TRIM(dci.item_name))'));
        })
        ->when($request->project_id, function ($q) use ($request) {
            $q->where('po.project_id', $request->project_id);
        })
        ->sum(DB::raw('dci.supplied_qty * COALESCE(poi.unit_price, 0)'));

    return response()->json([
        'status' => true,
        'data' => [
            'total_po_amount' => $total_po,
            'approved_amount' => $approved_po,
            'pending_amount'  => $pending_po,
            'received_amount' => $received_amount,
        ]
    ]);
}





/* how much qty receive on po  */


public function poWithQty(Request $request)
{
    $pos = DB::table('purchase_orders as po')
        ->leftJoin('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')

        // Join DC IN header
        ->leftJoin('dc_ins as dcih', 'dcih.purchase_order_id', '=', 'po.id')

        // Join DC IN items (match by item_name)
        ->leftJoin('dc_in_items as dci', function ($join) {
            $join->on('dci.dc_in_id', '=', 'dcih.id')
                 ->on(DB::raw('LOWER(TRIM(dci.item_name))'), '=', DB::raw('LOWER(TRIM(poi.item_name))'));
        })

        ->select(
            'po.id',
            'po.po_number',
            'po.total_amount',
            'po.approved_status',

            DB::raw('COALESCE(SUM(DISTINCT poi.ordered_qty),0) as total_ordered'),
            DB::raw('COALESCE(SUM(dci.supplied_qty),0) as total_received')
        )

        ->groupBy('po.id', 'po.po_number', 'po.total_amount', 'po.approved_status')
        ->get();

    // Calculate pending + %
    foreach ($pos as $po) {

        $po->pending_qty = $po->total_ordered - $po->total_received;

        $po->progress_percent = $po->total_ordered > 0
            ? round(($po->total_received / $po->total_ordered) * 100, 2)
            : 0;
    }

    return response()->json([
        'status' => true,
        'data' => $pos
    ]);
}




/****PO  status update  */
public function submit($id)
{
    $po = PurchaseOrder::findOrFail($id);

    // ✅ Only draft can be submitted
    if ($po->status !== 'draft') {
        return response()->json(['message' => 'Only draft PO can be submitted'], 400);
    }

    // ✅ Update status to pending approval
    $po->update([
        'status' => 'pending_approval'
    ]);

    // ✅ Send email to accounts team
    $approvers = User::whereIn('id', [81, 92, 93])->get();

    foreach ($approvers as $approver) {

        $this->sendMail(
            $approver->email,
            "PO Approval Required - {$po->po_number}",
            "
            <div style='font-family: Arial; background:#f4f6f9; padding:20px;'>
                <div style='max-width:600px; margin:auto; background:#fff; padding:20px;'>

                    <h2>Purchase Order Approval Required</h2>

                    <p>Hello <b>{$approver->name}</b>,</p>

                    <p>A Purchase Order has been submitted for approval.</p>

                    <p><b>PO Number:</b> {$po->po_number}</p>
                    <p><b>Amount:</b> {$po->total_amount}</p>

                    <p>Please login and approve.</p>

                    <a href='https://erp.htcl.co.in'>Open ERP</a>

                </div>
            </div>
            "
        );
    }

    return response()->json([
        'message' => 'PO submitted for approval & email sent'
    ]);
}

public function approve($id)
{
    $po = PurchaseOrder::findOrFail($id);

    // ✅ Only pending approval can be approved
    if ($po->status !== 'pending_approval') {
        return response()->json(['message' => 'PO is not pending approval'], 400);
    }

    $po->update([
        'status' => 'approved',
        'approved_by' => auth()->id(),
        'approved_status' => 'approved'
    ]);

    return response()->json([
        'message' => 'PO Approved successfully'
    ]);
}



/*** Reject PO */

public function reject(Request $request, $id)
{
    $request->validate([
        'reason' => 'nullable|string|max:500'
    ]);

    $po = PurchaseOrder::findOrFail($id);

    // ✅ Only pending approval can be rejected
    if ($po->status !== 'pending_approval') {
        return response()->json([
            'message' => 'Only pending approval PO can be rejected'
        ], 400);
    }

    $po->update([
        'status'            => 'rejected',
        'approved_by'       => auth()->id(),
        'approved_status'   => 'rejected',
      //  'rejection_reason'  => $request->reason ?? null,
    ]);

    // ✅ (Optional) Send email to creator
    if ($po->created_by) {

        $creator = User::find($po->created_by);

        if ($creator) {
            $this->sendMail(
                $creator->email,
                "PO Rejected - {$po->po_number}",
                "
                <div style='font-family: Arial; padding:20px;'>
                    <h2>Purchase Order Rejected</h2>

                    <p>Hello {$creator->name},</p>

                    <p>Your Purchase Order <b>{$po->po_number}</b> has been rejected.</p>

                    <p><b>Reason:</b> " . ($request->reason ?? 'Not provided') . "</p>

                    <p>Please review and update.</p>
                </div>
                "
            );
        }
    }

    return response()->json([
        'message' => 'PO Rejected successfully'
    ]);
}
}

