<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Politique de confidentialité - Route publique pour Google Play Console
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');
