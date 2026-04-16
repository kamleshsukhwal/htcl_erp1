<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\DcOut;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
//use PDF;

class InvoiceController extends Controller
{
    // 1. Create Invoice from DC
  public function createFromDc(Request $request)
{
    $request->validate([
        'dc_out_id' => 'required|exists:dc_outs,id',
        'state' => 'required|in:same,different'
    ]);

    // prevent duplicate
    if (Invoice::where('dc_out_id', $request->dc_out_id)->exists()) {
        return response()->json([
            'status' => false,
            'message' => 'Invoice already exists for this DC'
        ]);
    }

    // ✅ load boq relation
    $dc = DcOut::with('items.boqItem')->findOrFail($request->dc_out_id);

    $invoice = Invoice::create([
        'invoice_no' => 'INV-' . time(),
        'customer_id' => $dc->customer_id,
        'dc_out_id' => $dc->id,
        'invoice_date' => now(),
    ]);

    $total = 0;

    foreach ($dc->items as $item) {

        $itemName = $item->boqItem->item_name ?? 'N/A';
        $qty = $item->issued_qty ?? 0;
        $price = $item->boqItem->price ?? 0; // change if column name different

        $amount = $qty * $price;

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => $itemName,
            'qty' => $qty,
            'price' => $price,
            'amount' => $amount
        ]);

        $total += $amount;
    }

    // GST
    $gstPercent = 18;
    $gstAmount = ($total * $gstPercent) / 100;

    if ($request->state == 'same') {
        $cgst = $gstAmount / 2;
        $sgst = $gstAmount / 2;
        $igst = 0;
    } else {
        $cgst = 0;
        $sgst = 0;
        $igst = $gstAmount;
    }

    $invoice->update([
        'subtotal' => $total,
        'gst_percent' => $gstPercent,
        'cgst' => $cgst,
        'sgst' => $sgst,
        'igst' => $igst,
        'total' => $total + $gstAmount
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Invoice created successfully',
        'data' => $invoice
    ]);
}

    // 2. Invoice List
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Invoice::latest()->get()
        ]);
    }

    // 3. Invoice Details
    public function show($id)
    {
        $data = Invoice::with('items', 'payments')->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    // 4. Manual Invoice
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
            'invoice_date' => 'required|date',
            'state' => 'required|in:same,different',
            'items' => 'required|array',
            'items.*.item_name' => 'required|string',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:1',
        ]);

        $invoice = Invoice::create([
            'invoice_no' => 'INV-' . time(),
            'customer_id' => $request->customer_id,
            'invoice_date' => $request->invoice_date
        ]);

        $total = 0;

        foreach ($request->items as $item) {
            $amount = $item['qty'] * $item['price'];

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_name' => $item['item_name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'amount' => $amount
            ]);

            $total += $amount;
        }

        // ✅ GST Calculation
        $gstPercent = 18;
        $gstAmount = ($total * $gstPercent) / 100;

        if ($request->state == 'same') {
            $cgst = $gstAmount / 2;
            $sgst = $gstAmount / 2;
            $igst = 0;
        } else {
            $cgst = 0;
            $sgst = 0;
            $igst = $gstAmount;
        }

        $invoice->update([
            'subtotal' => $total,
            'gst_percent' => $gstPercent,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $total + $gstAmount
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Manual invoice created',
            'data' => $invoice
        ]);
    }


public function download($id)
{
    $invoice = Invoice::with(['items', 'payments'])->findOrFail($id);

    // Calculate totals
    $totalPaid = $invoice->payments->sum('amount');
    $balance = $invoice->total - $totalPaid;

    return response()->json([
        'status' => true,
        'data' => $invoice,
        'payment_summary' => [
            'invoice_total' => $invoice->total,
            'total_paid' => $totalPaid,
            'balance' => $balance,
            'payment_status' => $balance <= 0 ? 'PAID' : 'PARTIAL/UNPAID'
        ]
    ]);
}

    /*  pdf library issue
public function download($id)
{
    $invoice = Invoice::with('items')->findOrFail($id);

    $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));

    $fileName = 'invoices/'.$invoice->invoice_no.'.pdf';

    Storage::put('public/'.$fileName, $pdf->output());

    return response()->json([
        'status' => true,
        'message' => 'PDF generated successfully',
        'url' => asset('storage/'.$fileName)
    ]);
}
*/
    // 9. Cancel Invoice
    public function cancel($id)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->update(['status' => 'cancelled']);

        return response()->json([
            'status' => true,
            'message' => 'Invoice cancelled successfully'
        ]);
    }
}