<?php

use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentApprovalController;
use App\Http\Controllers\FolderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no auth required)
Route::post('/login', [LoginController::class, 'login']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/user', [LoginController::class, 'user']);

    // Document routes (all authenticated users)
    Route::apiResource('documents', DocumentController::class);
    Route::get('/groups', [GroupController::class, 'index']);
    
    // Document logs
    Route::get('/documents/{id}/logs', [DocumentController::class, 'logs']);
    
    // Document approvals
    Route::get('/documents/{id}/approvals', [DocumentApprovalController::class, 'index']);
    Route::post('/documents/{documentId}/approvals/{approvalId}/approve', [DocumentApprovalController::class, 'approve']);
    Route::post('/documents/{documentId}/approvals/{approvalId}/reject', [DocumentApprovalController::class, 'reject']);
    Route::put('/documents/{id}/approvals/sequence', [DocumentApprovalController::class, 'updateSequence']);
    
    // Folders
    Route::apiResource('folders', FolderController::class);
    Route::post('/documents/{id}/move', [FolderController::class, 'moveDocument']);

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::post('/groups', [GroupController::class, 'store']);
    });
});
