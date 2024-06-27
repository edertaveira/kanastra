<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessChunksJob;
use App\Jobs\ProcessCsvFile;
use App\Jobs\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use League\Csv\Statement;

class FileProcessingController extends Controller
{

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $path = $request->file('file')->store('uploads');
        ProcessChunksJob::dispatch($path);

        return response()->json(['message' => 'File uploaded and processing started.'], 200);
    }

}
