<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CampaignAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\ReportController;

// PUBLIC ROUTES
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/campaigns', [CampaignController::class, 'index']);
Route::get('/campaigns/{slug}', [CampaignController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

// AUTHENTICATED ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/campaigns', [CampaignController::class, 'store']);
    Route::put('/campaigns/{id}', [CampaignController::class, 'update']);
    Route::post('/donations', [DonationController::class, 'store']);
    Route::get('/donations/{orderId}', [DonationController::class, 'show']);
    Route::post('/campaigns/{id}/comments', [CommentController::class, 'store']);
});

// MIDTRANS WEBHOOK
Route::post('/donations/notification', [DonationController::class, 'notification']);

// ADMIN ROUTES
Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/campaigns', [CampaignAdminController::class, 'index']);
    Route::post('/campaigns/{id}/verify', [CampaignAdminController::class, 'verify']);
    Route::post('/campaigns/{id}/reject', [CampaignAdminController::class, 'reject']);
    Route::post('/campaigns/{id}/toggle-featured', [CampaignAdminController::class, 'toggleFeatured']);
    Route::delete('/campaigns/{id}', [CampaignAdminController::class, 'destroy']);
    Route::get('/users', [UserAdminController::class, 'index']);
    Route::get('/users/{id}', [UserAdminController::class, 'show']);
    Route::patch('/users/{id}/role', [UserAdminController::class, 'updateRole']);
    Route::post('/users/{id}/toggle-verified', [UserAdminController::class, 'toggleVerified']);
    Route::post('/users/{id}/suspend', [UserAdminController::class, 'suspend']);
    Route::post('/users/{id}/restore', [UserAdminController::class, 'restore']);
    Route::get('/reports/donations', [ReportController::class, 'donations']);
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/export', [ReportController::class, 'export']);
});