<?php

namespace App\Jobs;

use App\Models\PaymentSlip;
use App\StatusEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

ini_set('memory_limit', '256M');

class ProcessPaymentSlipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chunk;

    public function __construct($chunk)
    {
        $this->chunk = $chunk;
    }



    public function handle(): void
    {
        Log::info('Processing each block');
        foreach ($this->chunk as $record) {
            if (!isset($record['debtId']) || !isset($record['email']) || !isset($record['name'])) {
                Log::error('Invalid record structure', ['record' => $record]);
                continue;
            }
            if ($record['debtId']) {
                $paymentSlip = PaymentSlip::where('debtId', $record['debtId'])->first();
                if (!$paymentSlip) {
                    try {
                        DB::transaction(function () use ($record) {
                            PaymentSlip::insert($record);
                            Log::info('Payment Slip Generated', ['debtId' => $record['debtId']]);
                        });
                    } catch (\Exception $e) {
                        Log::error('Payment Slip Generated - ERROR', ['debtId' => $record['debtId'], 'error' => $e->getMessage()]);
                    }
                } else {
                    Log::warning('Payment Slip Already Processed', ['debtId' => $record['debtId']]);
                }
            }
        }
        ProcessEmailsJob::dispatch($this->chunk)->delay(now()->addMinutes(2));
    }
}
