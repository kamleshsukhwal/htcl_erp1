<?php
namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;
/**** Controller for Billing payment after invoice */
class PaymentController extends Controller
{
    // 5. Add Payment

/*public function store(Request $request)
{
    $request->validate([
        'invoice_id' => 'required|exists:invoices,id',
        'amount' => 'required|numeric|min:1',
        'payment_date' => 'required|date',
        'mode' => 'required',
        'txn_ref_no' => 'nullable|string|max:255',
        'attachment' => 'nullable|file|max:10240' // 10MB
    ]);

    $data = $request->all();

    // ✅ Store file in PRIVATE storage
    if ($request->hasFile('attachment')) {

        $file = $request->file('attachment');
        $fileName = time() . '_' . $file->getClientOriginalName();

        // 🔥 SAME as your BOQ logic
        $path = $file->storeAs(
            "payments/{$request->invoice_id}",
            $fileName,
            'private'
        );

        $data['attachment'] = $path;
    }

    // ✅ Save payment
    $payment = Payment::create($data);

    // ✅ Update invoice
    $invoice = Invoice::find($request->invoice_id);
    $paid = $invoice->payments()->sum('amount');

    if ($paid >= $invoice->total) {
        $status = 'paid';
    } elseif ($paid > 0) {
        $status = 'partial';
    } else {
        $status = 'pending';
    }

    $invoice->update([
        'paid_amount' => $paid,
        'status' => $status
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Payment added successfully',
        'data' => $payment
    ]);
}*/

public function store(Request $request)
{
    try {
 
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'mode' => 'required',
            'txn_ref_no' => 'nullable|string|max:255',
          //  'attachment' => 'nullable|file|max:10240'
        ]);

        $data = $request->all();

        
        $payment = Payment::create($data);

        $invoice = Invoice::find($request->invoice_id);
        $paid = $invoice->payments()->sum('amount');

        if ($paid >= $invoice->total) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        } else {
            $status = 'pending';
        }

        $invoice->update([
            'paid_amount' => $paid,
            'status' => $status
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Payment added successfully',
            'data' => $payment
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'status' => false,
            'message' => $e->getMessage(), // 🔥 SHOW ACTUAL ERROR
            'line' => $e->getLine(),
        ], 500);
    }
}


/*** payment receipt upload */

 

public function paymentreceiptupload(Request $request, $paymentId)
{
    $payment = Payment::findOrFail($paymentId);

    $request->validate([
        'attachment' => 'required|file|max:10240'
    ]);

    // delete old file (optional but recommended)
    if ($payment->attachment && file_exists(storage_path('app/private/'.$payment->attachment))) {
        unlink(storage_path('app/private/'.$payment->attachment));
    }

    $file = $request->file('attachment');
    $fileName = time().'_'.$file->getClientOriginalName();

    $path = $file->storeAs(
        "payments/{$paymentId}",
        $fileName,
        'private'
    );

    $payment->update([
        'attachment' => $path
    ]);

    return response()->json([
        'status' => true,
        'message' => 'File uploaded successfully',
        'data' => $payment
    ]);
}

/*** download payment receipt */

public function downloadpaymentrecipt($id)
{
    $payment = Payment::findOrFail($id);

    if (!$payment->attachment) {
        return response()->json(['message' => 'No file found'], 404);
    }

    return response()->download(
        storage_path('app/private/' . $payment->attachment)
    );
}

    /*  store payment method in the private repo
    public function store(Request $request)
    {
        Payment::create($request->all());

        $invoice = Invoice::find($request->invoice_id);
        $paid = $invoice->payments()->sum('amount');

        if ($paid >= $invoice->total) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        } else {
            $status = 'pending';
        }

        $invoice->update([
            'paid_amount'=>$paid,
            'status'=>$status
        ]);

        return response()->json(['status'=>true]);
    }*/

    // 6. Payment History
    public function list($id)
    {
        return response()->json([
            'status'=>true,
            'data'=>Payment::where('invoice_id',$id)->get()
        ]);
    }
}