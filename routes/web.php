<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Politique de confidentialité - Route publique pour Google Play Console
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

// Routes pour la demande de suppression de compte
Route::get('/account-deletion-request', [App\Http\Controllers\AccountDeletionController::class, 'showForm'])->name('account-deletion.form');
Route::post('/account-deletion-request', [App\Http\Controllers\AccountDeletionController::class, 'requestDeletion'])->name('account-deletion.submit');
Route::get('/account-deletion-success', [App\Http\Controllers\AccountDeletionController::class, 'success'])->name('account-deletion.success');
