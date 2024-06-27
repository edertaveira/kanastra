<?php

namespace App\Jobs;

use App\Mail\CustomEmail;
use App\Models\PaymentSlip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chunk;

    public function __construct($chunk)
    {
        $this->chunk = $chunk;
    }

    public function handle(): void
    {
        Log::info('Processing Email Sending', []);
        foreach ($this->chunk as $record) {
            $paymentslip = PaymentSlip::where('debtId', $record['debtId'])->first();
            if ($paymentslip && $paymentslip->status !== 'sent') {
                try {
                    Mail::to($record['email'])->send(new CustomEmail($record['name'], "http://kanastra.com.br/paymentslip/" . $record['debtId']));
                    $paymentslip->status = 'sent';
                    Log::info('Email Sent', ['email' => $record['email'], 'debtId' => $record['debtId']]);
                } catch (\Exception $e) {
                    $paymentslip->status = 'error';
                    Log::error('Error to send the email', [
                        'email' => $record['email'],
                        'error' => $e->getMessage()
                    ]);
                }
                $paymentslip->save();
            } elseif ($paymentslip) {
                Log::warning('Email already sent.', ['email' => $record['email'], 'debtId' => $record['debtId']]);
            } else {
                Log::error('Email wasn`t send - Debit not found', ['email' => $record['email'], 'debtId' => $record['debtId']]);
            }
        }
    }
}
