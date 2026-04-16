<?php
namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;

class PaymentController extends Controller
{
    // 5. Add Payment
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
    }

    // 6. Payment History
    public function list($id)
    {
        return response()->json([
            'status'=>true,
            'data'=>Payment::where('invoice_id',$id)->get()
        ]);
    }
}