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
        'status'        => 'required|string',

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

    try {

        // ✅ Calculate total
        $calculatedBaseTotal = 0;

        foreach ($request->items as $item) {
            $calculatedBaseTotal += ($item['ordered_qty'] * $item['unit_price']);
        }

        $gstAmount = $request->gst_amount ?? ($calculatedBaseTotal * 0.18);
        $finalTotal = $calculatedBaseTotal + $gstAmount;

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
            'status'        => $request->status,
            't_c'           => $request->t_c,
            'notes'         => $request->notes,
        ]);

        // =========================
        // ✅ Insert Items + BOQ logic
        // =========================

        foreach ($request->items as $item) {

            $boqItemId = null;

            // ✅ If save_to_master → insert into BOQ
            if (!empty($item['save_to_master']) && empty($item['boq_item_id'])) {

                $boq = BoqItem::create([
                    'item_name'  => $item['item_name'],
                    'project_id' => $request->project_id
                ]);

                $boqItemId = $boq->id;

            } else {
                // existing BOQ item
                $boqItemId = $item['boq_item_id'] ?? null;
            }

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

        $approvers = User::whereIn('id', [81, 92, 93])->get();

        foreach ($approvers as $approver) {

            $this->sendMail(
                $approver->email,
                "PO Approval Required - {$po->po_number}",
                "
                <div style='font-family: Arial, sans-serif; background:#f4f6f9; padding:20px;'>
                    <div style='max-width:600px; margin:auto; background:#ffffff; border-radius:8px; padding:25px;'>

                        <div style='text-align:center; margin-bottom:20px;'>
                            <img src='https://erp.htcl.co.in/logo_htcl.png' style='max-height:60px;'>
                        </div>

                        <h2 style='color:#2c3e50; text-align:center;'>Purchase Order Approval</h2>

                        <p>Hello <b>{$approver->name}</b>,</p>

                        <p>A new Purchase Order has been created and requires your approval.</p>

                        <div style='background:#f8f9fa; padding:15px; border-radius:6px; margin:15px 0;'>
                            <p><b>PO Number:</b> {$po->po_number}</p>
                            <p><b>Amount:</b> {$finalTotal}</p>
                        </div>

                        <p style='font-weight:bold; color:#d9534f;'>
                            Kindly login to HTCL ERP and approve the Purchase Order.
                        </p>

                        <p>
                            ERP URL:
                            <a href='https://erp.htcl.co.in'>https://erp.htcl.co.in</a>
                        </p>

                        <hr>

                        <p style='font-size:12px; color:#888; text-align:center;'>
                            This is an automated message from HTCL ERP System.
                        </p>

                    </div>
                </div>
                "
            );
        }

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
}