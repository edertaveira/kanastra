<?php

namespace Tests\Feature;

use App\Jobs\ProcessChunksJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FileProcessingControllerTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_accept_csv_file_and_dispatch_process_chunks_job()
    {
        Queue::fake();
        $file = UploadedFile::fake()->create('test.csv', 1024, 'text/csv');
        $response = $this->postJson('/api/upload', [
            'file' => $file,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'File uploaded and processing started.']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_error_if_no_file_provided()
    {
        $response = $this->postJson('/api/upload');
        $response->assertStatus(400);
        $response->assertJsonStructure(['error']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_error_if_invalid_file_type_provided()
    {
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        $response = $this->postJson('/api/upload', [
            'file' => $file,
        ]);
        $response->assertStatus(400);
        $response->assertJsonStructure(['error']);
    }
}
