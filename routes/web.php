<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/workout-plan/{workoutPlan}', [App\Http\Controllers\WorkoutController::class, 'show']);
Route::get('/workout-plan/{workoutPlan}/download', [App\Http\Controllers\WorkoutController::class, 'download'])->name('workout-plan.download');

Route::get('/meal-plan/{mealPlan}', [App\Http\Controllers\MealController::class, 'show']);
Route::get('/meal-plan/{mealPlan}/download', [App\Http\Controllers\MealController::class, 'download'])->name('meal-plan.download');

require __DIR__.'/auth.php';
