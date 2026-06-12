<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'home/Index')->name('home');

require __DIR__.'/admin.php';
