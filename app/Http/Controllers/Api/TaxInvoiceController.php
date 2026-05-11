<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaxInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class TaxInvoiceController extends Controller
{
    

/**********Store API ******/
public function store(Request $request)
{
    $request->validate([
        'purchase_order_id' => 'required|exists:purchase_orders,id',
        'invoice_no' => 'required|unique:tax_invoices,invoice_no',
        'invoice_date' => 'required|date',
        'invoice_amount' => 'required|numeric',
        'gst_amount' => 'nullable|numeric',
        'tds_amount' => 'nullable|numeric',
        'invoice_file' => 'nullable|mimes:pdf,xlsx,xls,jpg,jpeg,png|max:5120'
    ]);

    $filePath = null;

    if ($request->hasFile('invoice_file')) {

        $file = $request->file('invoice_file');

        $fileName = time().'_'.$file->getClientOriginalName();

        $filePath = $file->storeAs(
            'tax_invoices',
            $fileName,
            'public'
        );
    }

    $payableAmount =
        $request->invoice_amount
        + ($request->gst_amount ?? 0)
        - ($request->tds_amount ?? 0);

    $invoice = TaxInvoice::create([

        'purchase_order_id' => $request->purchase_order_id,

        'invoice_no' => $request->invoice_no,

        'invoice_date' => $request->invoice_date,

        'invoice_amount' => $request->invoice_amount,

        'gst_amount' => $request->gst_amount ?? 0,

        'tds_amount' => $request->tds_amount ?? 0,

        'payable_amount' => $payableAmount,

        'remarks' => $request->remarks,

        'invoice_file' => $filePath,

        'uploaded_by' => auth()->id(),
    ]);

    return response()->json([
        'message' => 'Tax invoice uploaded successfully',
        'data' => $invoice
    ]);
}


/**** List */
public function index($poId)
{
    $invoices = TaxInvoice::where('purchase_order_id', $poId)
                    ->latest()
                    ->get();

    return response()->json($invoices);
}

/**** view file */
public function viewFile($id)
{
    $invoice = TaxInvoice::findOrFail($id);

    return response()->json([
        'file_url' => asset('storage/' . $invoice->invoice_file)
    ]);
}


/*** show signel tax invoice */


public function show($id)
{
    $invoice = TaxInvoice::with('purchaseOrder')
        ->findOrFail($id);

    return response()->json([
        'message' => 'Invoice details fetched successfully',
        'data' => $invoice
    ]);
}

/***  update invoice */
public function update(Request $request, $id)
{
    $invoice = TaxInvoice::findOrFail($id);

    $request->validate([
        'invoice_no' => 'required|unique:tax_invoices,invoice_no,' . $id,
        'invoice_date' => 'required|date',
        'invoice_amount' => 'required|numeric',
        'gst_amount' => 'nullable|numeric',
        'tds_amount' => 'nullable|numeric',
        'invoice_file' => 'nullable|mimes:pdf,xlsx,xls,jpg,jpeg,png|max:5120'
    ]);

    $filePath = $invoice->invoice_file;

    if ($request->hasFile('invoice_file')) {
        $file = $request->file('invoice_file');
        $fileName = time() . '_' . $file->getClientOriginalName();

        $filePath = $file->storeAs('tax_invoices', $fileName, 'public');
    }

    $payableAmount =
        $request->invoice_amount
        + ($request->gst_amount ?? 0)
        - ($request->tds_amount ?? 0);

    $invoice->update([
        'invoice_no' => $request->invoice_no,
        'invoice_date' => $request->invoice_date,
        'invoice_amount' => $request->invoice_amount,
        'gst_amount' => $request->gst_amount ?? 0,
        'tds_amount' => $request->tds_amount ?? 0,
        'payable_amount' => $payableAmount,
        'remarks' => $request->remarks,
        'invoice_file' => $filePath,
    ]);

    return response()->json([
        'message' => 'Invoice updated successfully',
        'data' => $invoice
    ]);
}


/*** delete invoice */


public function destroy($id)
{
    $invoice = TaxInvoice::findOrFail($id);
    $invoice->delete();

    return response()->json([
        'message' => 'Invoice deleted successfully'
    ]);
}



/***download invoice */



public function downloadFile($id)
{
    $invoice = TaxInvoice::findOrFail($id);

    if (!$invoice->invoice_file) {
        return response()->json(['message' => 'File not found'], 404);
    }

    return Storage::disk('public')->download($invoice->invoice_file);
}



/*** summary */

public function summary($poId)
{
    $data = TaxInvoice::where('purchase_order_id', $poId)
        ->selectRaw('
            COUNT(*) as total_invoices,
            SUM(invoice_amount) as total_invoice_amount,
            SUM(gst_amount) as total_gst,
            SUM(tds_amount) as total_tds,
            SUM(payable_amount) as total_payable
        ')
        ->first();

    return response()->json([
        'message' => 'Summary fetched successfully',
        'data' => $data
    ]);
}


}
