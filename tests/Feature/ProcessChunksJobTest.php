<?php

namespace Tests\Unit;

use App\Jobs\ProcessChunksJob;
use App\Jobs\ProcessPaymentSlipJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use Tests\TestCase;

class ProcessChunksJobTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_process_csv_in_chunks_and_dispatch_process_payment_slip_job()
    {
        Queue::fake();
        $csvPath = 'test.csv';
        Storage::fake('local');
        $csv = Writer::createFromPath(Storage::path($csvPath), 'w+');
        $csv->insertOne(['name', 'governmentId', 'email', 'debtAmount', 'debtDueDate', 'debtId']);
        for ($i = 0; $i < 250; $i++) {
            $csv->insertOne(["value1-$i", "value2-$i", "value3-$i", "value4-$i", "value5-$i", "value6-$i"]);
        }
        $job = new ProcessChunksJob($csvPath);
        $job->handle();
        Queue::assertPushed(ProcessPaymentSlipJob::class, 3);
    }
}
