<?php

use App\Http\Controllers\FileProcessingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/read', [FileProcessingController::class, 'read']);
Route::post('/upload', [FileProcessingController::class, 'upload']);

