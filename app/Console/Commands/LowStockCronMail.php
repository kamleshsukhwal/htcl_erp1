<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class LowStockCronMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test';

    protected $description = 'Test cron email';

    public function handle()
    {
        Mail::raw('Cron job email working successfully', function ($message) {

            $message->to('kamleshsukhwal5@gmail.com')
                    ->subject('Laravel Cron Test');
        });

        $this->info('Test mail sent');
    }
}
