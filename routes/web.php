<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use Illuminate\Support\Facades\Storage;

Route::get('/test-b2', function () {
    Storage::disk('b2')->put('test.txt', 'This is a test.');
    return 'تم رفع الملف إلى Backblaze';
});




