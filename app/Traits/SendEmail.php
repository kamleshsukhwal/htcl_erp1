<?php

namespace App\Traits;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

trait SendEmail
{
    public function sendMail(string $to, string $subject, string $content): void
    {
        try {
            Mail::send('emails.common', ['content' => $content], function ($message) use ($to, $subject) {
                $message->to($to)
                        ->subject($subject);
            });
        } catch (Exception $e) {
            Log::error("Email sending failed: " . $e->getMessage());
            // Optionally, handle error gracefully
        }
    }


    public function sendMailWithMultipleAttachments($to, $subject, $content, $files): void
    {
        try {
            Mail::send('emails.common', ['content' => $content], function ($message) use ($to, $subject, $files) {

                $message->to($to)
                        ->subject($subject);

                // ✅ Loop all files
                foreach ($files as $file) {
                    $message->attach(
                        $file->getRealPath(),
                        [
                            'as'   => $file->getClientOriginalName(),
                            'mime' => $file->getMimeType()
                        ]
                    );
                }
            });

        } catch (\Exception $e) {
            \Log::error("Email with multiple attachments failed: " . $e->getMessage());
        }
    }
}   