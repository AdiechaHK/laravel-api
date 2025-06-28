<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('user-profile', [AuthController::class, 'userProfile']);
    
    Route::apiResource('posts', PostController::class);
    Route::apiResource('posts.comments', CommentController::class)->shallow();
});

// Example #1
Route::get('test', function () {
    $user = App\Models\User::first();
    // if (!$user instanceof App\Models\User) {
    //     throw new \RuntimeException('User is not an instance of App\Models\User');
    // }
    return [
        'username' => $user->name,
        'email' => $user->eamil,
        // 'email' => $user->email,
    ];
});

// Example #2
Route::get('test2', function () {
    $user = App\Models\User::first();
    if (!$user instanceof App\Models\User) {
        throw new \RuntimeException('User is not an instance of App\Models\User');
    }
    return [
        'age' => $user->age(),
    ];
});