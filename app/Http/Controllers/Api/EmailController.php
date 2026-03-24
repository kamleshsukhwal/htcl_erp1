<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    use SendEmail;

    public function sendWithAttachment(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'to'        => 'required|email',
            'subject'   => 'required|string|max:255',
            'content'   => 'required|string',
            'files'     => 'required|array', // ✅ multiple files
            'files.*'   => 'file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // ✅ Get multiple files
        $files = $request->file('files');

        $this->sendMailWithMultipleAttachments(
            $request->to,
            $request->subject,
            $request->content,
            $files
        );

        return response()->json([
            'status'  => true,
            'message' => 'Email sent successfully with multiple attachments.',
        ]);
    }
}