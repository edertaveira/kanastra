<?php

namespace Tests\Feature;

use App\Jobs\ProcessChunksJob;
use App\Jobs\ProcessEmailsJob;
use App\Jobs\ProcessPaymentSlipJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileProcessingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_process_csv_file_and_dispatch_all_jobs()
    {
        Queue::fake();
        $csvContent = "debtId,email,name\n1,user1@example.com,User 1\n2,user2@example.com,User 2\n";
        Storage::fake('local');
        Storage::put('uploads/test.csv', $csvContent);
        $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);


        $response = $this->postJson('/api/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'File uploaded and processing started.']);
        Queue::assertPushed(ProcessChunksJob::class, function ($job) use ($file) {
            return strpos($job->path, $file->hashName()) !== false;
        });
        Queue::assertPushed(ProcessChunksJob::class, function ($job) {
            $job->handle();
            return true;
        });
        Queue::assertPushed(ProcessPaymentSlipJob::class);
        Queue::assertPushed(ProcessPaymentSlipJob::class, function ($job) {
            $job->handle();
            return true;
        });
        Queue::assertPushed(ProcessEmailsJob::class);
    }
}
