<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', function () {
    return view('tree');
});

// Route::get('/folders/flat', [FolderController::class, 'flat']);
