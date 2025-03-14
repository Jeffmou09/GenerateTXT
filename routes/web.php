<?php

use App\Http\Controllers\FileParserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FileParserController::class, 'index'])->name('home');
Route::post('/upload', [FileParserController::class, 'upload'])->name('upload');
Route::get('/view-file/{filename}', [FileParserController::class, 'viewFile'])->name('view.file');
Route::delete('/files/{file}', [FileParserController::class, 'deleteFile'])->name('delete.file');