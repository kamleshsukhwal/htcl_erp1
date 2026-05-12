<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaxInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaxInvoiceController extends Controller
{

    /********** STORE API ******/
    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'invoice_no'        => 'required|unique:tax_invoices,invoice_no',
            'invoice_date'      => 'required|date',
            'invoice_amount'    => 'required|numeric',
            'gst_amount'        => 'nullable|numeric',
            'tds_amount'        => 'nullable|numeric',
            'remarks'           => 'nullable|string',

            // ✅ PRIVATE FILE VALIDATION
            'invoice_file'      => 'nullable|mimes:pdf,xlsx,xls,jpg,jpeg,png|max:5120'
        ]);

        $filePath = null;

        // ✅ STORE FILE IN PRIVATE FOLDER
        if ($request->hasFile('invoice_file')) {

            $file = $request->file('invoice_file');

            $fileName = time() . '_' . $file->getClientOriginalName();

            $filePath = $file->storeAs(
                'tax_invoices',
                $fileName,
                'private'
            );
        }

        // ✅ CALCULATE PAYABLE
        $payableAmount =
            $request->invoice_amount
            + ($request->gst_amount ?? 0)
            - ($request->tds_amount ?? 0);

        // ✅ SAVE DB
        $invoice = TaxInvoice::create([

            'purchase_order_id' => $request->purchase_order_id,

            'invoice_no'        => $request->invoice_no,

            'invoice_date'      => $request->invoice_date,

            'invoice_amount'    => $request->invoice_amount,

            'gst_amount'        => $request->gst_amount ?? 0,

            'tds_amount'        => $request->tds_amount ?? 0,

            'payable_amount'    => $payableAmount,

            'remarks'           => $request->remarks,

            'invoice_file'      => $filePath,

            'status'            => 'uploaded',

            'uploaded_by'       => auth()->id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Tax invoice uploaded successfully',
            'data'    => $invoice
        ]);
    }


    /********** LIST ALL BY PO ******/
    public function index($poId)
    {
        $invoices = TaxInvoice::where('purchase_order_id', $poId)
            ->latest()
            ->get();

        // ✅ ADD VIEW + DOWNLOAD URL
        $invoices->transform(function ($invoice) {

            $invoice->view_url = url(
                'api/tax-invoice/view/' . $invoice->id
            );

            $invoice->download_url = url(
                'api/tax-invoice/download/' . $invoice->id
            );

            return $invoice;
        });

        return response()->json([
            'status' => true,
            'count'  => $invoices->count(),
            'data'   => $invoices
        ]);
    }


    /********** VIEW FILE ******/
    public function viewFile($id)
    {
        $invoice = TaxInvoice::findOrFail($id);

        if (!$invoice->invoice_file) {

            return response()->json([
                'status' => false,
                'message' => 'File not found'
            ], 404);
        }

        // ✅ PRIVATE FILE PATH
        $path = Storage::disk('private')->path($invoice->invoice_file);

        if (!file_exists($path)) {

            return response()->json([
                'status' => false,
                'message' => 'File not found'
            ], 404);
        }

        return response()->file($path);
    }


    /********** SHOW SINGLE TAX INVOICE ******/
    public function show($id)
    {
        $invoice = TaxInvoice::with('purchaseOrder')
            ->findOrFail($id);

        // ✅ ADD URLS
        $invoice->view_url = url(
            'api/tax-invoice/view/' . $invoice->id
        );

        $invoice->download_url = url(
            'api/tax-invoice/download/' . $invoice->id
        );

        return response()->json([
            'status'  => true,
            'message' => 'Invoice details fetched successfully',
            'data'    => $invoice
        ]);
    }


    /********** UPDATE INVOICE ******/
    public function update(Request $request, $id)
    {
        $invoice = TaxInvoice::findOrFail($id);

        $request->validate([
            'invoice_no'     => 'required|unique:tax_invoices,invoice_no,' . $id,
            'invoice_date'   => 'required|date',
            'invoice_amount' => 'required|numeric',
            'gst_amount'     => 'nullable|numeric',
            'tds_amount'     => 'nullable|numeric',
            'remarks'        => 'nullable|string',

            // ✅ PRIVATE FILE VALIDATION
            'invoice_file'   => 'nullable|mimes:pdf,xlsx,xls,jpg,jpeg,png|max:5120'
        ]);

        $filePath = $invoice->invoice_file;

        // ✅ IF NEW FILE UPLOADED
        if ($request->hasFile('invoice_file')) {

            // ✅ DELETE OLD FILE
            if (
                $invoice->invoice_file &&
                Storage::disk('private')->exists($invoice->invoice_file)
            ) {
                Storage::disk('private')->delete($invoice->invoice_file);
            }

            $file = $request->file('invoice_file');

            $fileName = time() . '_' . $file->getClientOriginalName();

            // ✅ STORE IN PRIVATE
            $filePath = $file->storeAs(
                'tax_invoices',
                $fileName,
                'private'
            );
        }

        // ✅ CALCULATE PAYABLE
        $payableAmount =
            $request->invoice_amount
            + ($request->gst_amount ?? 0)
            - ($request->tds_amount ?? 0);

        // ✅ UPDATE
        $invoice->update([

            'invoice_no'     => $request->invoice_no,

            'invoice_date'   => $request->invoice_date,

            'invoice_amount' => $request->invoice_amount,

            'gst_amount'     => $request->gst_amount ?? 0,

            'tds_amount'     => $request->tds_amount ?? 0,

            'payable_amount' => $payableAmount,

            'remarks'        => $request->remarks,

            'invoice_file'   => $filePath,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Invoice updated successfully',
            'data'    => $invoice
        ]);
    }


    /********** DELETE INVOICE ******/
    public function destroy($id)
    {
        $invoice = TaxInvoice::findOrFail($id);

        // ✅ DELETE PRIVATE FILE
        if (
            $invoice->invoice_file &&
            Storage::disk('private')->exists($invoice->invoice_file)
        ) {
            Storage::disk('private')->delete($invoice->invoice_file);
        }

        // ✅ DELETE DB RECORD
        $invoice->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Invoice deleted successfully'
        ]);
    }


    /********** DOWNLOAD FILE ******/
    public function downloadFile($id)
    {
        $invoice = TaxInvoice::findOrFail($id);

        if (
            !$invoice->invoice_file ||
            !Storage::disk('private')->exists($invoice->invoice_file)
        ) {

            return response()->json([
                'status' => false,
                'message' => 'File not found'
            ], 404);
        }

        return Storage::disk('private')->download(
            $invoice->invoice_file
        );
    }


    /********** SUMMARY ******/
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
            'status'  => true,
            'message' => 'Summary fetched successfully',
            'data'    => $data
        ]);
    }
}