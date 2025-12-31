<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// AUTHENTICATION
Route::prefix('auth')->group(function () {
	Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);
	Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
	Route::post('send-otp', [App\Http\Controllers\AuthController::class, 'sendOtp']);
	Route::post('verify-otp', [App\Http\Controllers\AuthController::class, 'verifyOtp']);
	Route::post('google', [App\Http\Controllers\AuthController::class, 'google']);
	Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// ROUTES PROTEGEES (auth:sanctum)
Route::middleware(['auth:sanctum'])->group(function () {
	// Utilisateur connecté
	Route::get('users/me', [App\Http\Controllers\UserController::class, 'me']);
	Route::patch('users/me', [App\Http\Controllers\UserController::class, 'updateMe']);
	Route::post('users/me/avatar', [App\Http\Controllers\UserController::class, 'uploadAvatar']);
	Route::post('users/search', [App\Http\Controllers\UserController::class, 'search']);
	Route::delete('users/me', [App\Http\Controllers\AccountDeletionController::class, 'deleteMyAccount']);

	// Couples
	Route::get('couples/me', [App\Http\Controllers\CoupleController::class, 'myCouple']);
	Route::post('couples/break-up', [App\Http\Controllers\CoupleController::class, 'breakUp']);

	// Couple Requests
	Route::get('couple-requests/received', [App\Http\Controllers\CoupleRequestController::class, 'received']);
	Route::get('couple-requests/sent', [App\Http\Controllers\CoupleRequestController::class, 'sent']);
	Route::post('couple-requests/send', [App\Http\Controllers\CoupleRequestController::class, 'sendRequest']);
	Route::post('couple-requests/{id}/respond', [App\Http\Controllers\CoupleRequestController::class, 'respond']);

	// Recherche
	Route::post('search/user', [App\Http\Controllers\SearchHistoryController::class, 'searchUser']);
	Route::get('search/history', [App\Http\Controllers\SearchHistoryController::class, 'history']);

	// Statistiques
	Route::get('stats/user', [App\Http\Controllers\StatsController::class, 'userStats']);
	Route::get('stats/global', [App\Http\Controllers\StatsController::class, 'globalStats']);
	Route::get('stats/profile-viewers', [App\Http\Controllers\StatsController::class, 'profileViewers']);

	// Abonnements
	Route::get('subscriptions/me', [App\Http\Controllers\SubscriptionController::class, 'mySubscription']);
	Route::post('subscriptions/subscribe', [App\Http\Controllers\SubscriptionController::class, 'subscribe']);

	// Notifications
	Route::get('notifications/unread/count', [App\Http\Controllers\NotificationController::class, 'unreadCount']);
	Route::get('notifications/me', [App\Http\Controllers\NotificationController::class, 'myNotifications']);
	Route::post('notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
	Route::post('notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);

	// Profile Views (protégé)
	Route::post('profile-views', [App\Http\Controllers\ProfileViewController::class, 'store']);
});

// API Resource routes (CRUD)
Route::apiResource('users', App\Http\Controllers\UserController::class);
Route::apiResource('couples', App\Http\Controllers\CoupleController::class);
// Route::apiResource('couple-requests', App\Http\Controllers\CoupleRequestController::class); // Désactivé - routes spécifiques utilisées
Route::apiResource('search-histories', App\Http\Controllers\SearchHistoryController::class);
// Route::apiResource('profile-views', App\Http\Controllers\ProfileViewController::class); // Désactivé - route spécifique utilisée ci-dessus
Route::apiResource('subscriptions', App\Http\Controllers\SubscriptionController::class);
Route::apiResource('notifications', App\Http\Controllers\NotificationController::class);
