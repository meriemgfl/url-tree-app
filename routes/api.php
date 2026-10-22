<?php
// routes/api.php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\FolderController;
use Illuminate\Support\Facades\Route;

Route::post('/bookmarks/preview', [BookmarkController::class, 'preview']);
Route::get('/folders/flat', [FolderController::class, 'flat']);
Route::apiResource('bookmarks', BookmarkController::class)->only(['index', 'store', 'update', 'destroy']);
Route::apiResource('folders', FolderController::class)->only(['index', 'store', 'update', 'destroy']);
Route::get('/folders/flat', [FolderController::class, 'flat']);