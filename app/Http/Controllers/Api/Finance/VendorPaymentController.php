<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\VendorPayment;
use Illuminate\Http\Request;

class VendorPaymentController extends Controller
{
    


public function store(Request $request)
{
    $request->validate([
        'po_id' => 'required|exists:purchase_orders,id',
        'vendor_id' => 'required|exists:vendors,id',
        'amount' => 'required|numeric|min:1',
        'payment_date' => 'required|date',
        'mode' => 'required|string',
        'txn_ref_no' => 'nullable|string|max:255'
    ]);

    $payment = VendorPayment::create([
        'po_id' => $request->po_id,
        'vendor_id' => $request->vendor_id,
        'amount' => $request->amount,
        'payment_date' => $request->payment_date,
        'mode' => $request->mode,
        'txn_ref_no' => $request->txn_ref_no
    ]);

    // ✅ Update PO payment status
    $paid = VendorPayment::where('po_id', $request->po_id)->sum('amount');
    $po = PurchaseOrder::find($request->po_id);



$paid = VendorPayment::where('po_id', $request->po_id)->sum('amount');
$po = PurchaseOrder::find($request->po_id);

if (($paid + $request->amount) > $po->total_amount) {
    return response()->json([
        'status' => false,
        'message' => 'Payment exceeds PO total amount'
    ], 400);
}

    if ($paid >= $po->total_amount) {
        $status = 'paid';
    } elseif ($paid > 0) {
        $status = 'partial';
    } else {
        $status = 'pending';
    }

    $po->update([
        'paid_amount' => $paid,
        'payment_status' => $status
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Payment saved successfully',
        'data' => $payment
    ]);
}

public function download($id)
{
    $payment = VendorPayment::findOrFail($id);

    if (!$payment->attachment) {
        return response()->json(['message' => 'No file found'], 404);
    }

    return response()->download(
        storage_path('app/private/' . $payment->attachment)
    );
}

public function uploadAttachment(Request $request, $paymentId)
{
    $request->validate([
        'attachment' => 'required|file|max:10240'
    ]);

    $payment = VendorPayment::findOrFail($paymentId);

    $file = $request->file('attachment');
    $fileName = time().'_'.$file->getClientOriginalName();

    $path = $file->storeAs(
        "vendor_payments/{$paymentId}",
        $fileName,
        'private'
    );

    // ✅ Update payment record
    $payment->update([
        'attachment' => $path
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Attachment uploaded successfully',
        'file_path' => $path
    ]);
}



public function history($poId)
{
    $payments = VendorPayment::where('po_id', $poId)
        ->latest()
        ->get();

    $totalPaid = $payments->sum('amount');

    $po = PurchaseOrder::findOrFail($poId);

    $remaining = $po->total_amount - $totalPaid;

    return response()->json([
        'status' => true,
        'po_total' => $po->total_amount,
        'total_paid' => $totalPaid,
        'remaining_amount' => $remaining,
        'data' => $payments
    ]);
}


}
