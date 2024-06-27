<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Statement;

class ProcessChunksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $path;
    protected $chunk = [];

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function handle(): void
    {
        $csv = Reader::createFromPath(Storage::path($this->path), 'r');
        $csv->setHeaderOffset(0);
        $stmt = (new Statement());
        $chunkSize = 100;
        $index = 0;

        foreach ($stmt->process($csv) as $record) {
            $this->chunk[] = $record;
            if (count($this->chunk) >= $chunkSize) {
                ProcessPaymentSlipJob::dispatch($this->chunk)->delay(now()->addMinutes($index * 2));
                $this->chunk = [];
                $index++;
            }
        }

        if (!empty($this->chunk)) {
            ProcessPaymentSlipJob::dispatch($this->chunk)->delay(now()->addMinutes($index * 2));
        }
    }

    public function getChunk()
    {
        return $this->chunk;
    }
}
