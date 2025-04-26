<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::view('/', 'home');


Route::get('/workout-plan/{workoutPlan}', [App\Http\Controllers\WorkoutController::class, 'show']);
Route::get('/workout-plan/{workoutPlan}/download', [App\Http\Controllers\WorkoutController::class, 'download'])->name('workout-plan.download');

Route::get('/meal-plan/{mealPlan}', [App\Http\Controllers\MealController::class, 'show']);
Route::get('/meal-plan/{mealPlan}/download', [App\Http\Controllers\MealController::class, 'download'])->name('meal-plan.download');


// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Workout Plan Routes
    Route::get('/workout-plans', [App\Http\Controllers\WorkoutPlanController::class, 'index'])->name('workout-plans.index');
    Route::get('/workout-plans/{workoutPlan}', [App\Http\Controllers\WorkoutPlanController::class, 'show'])->name('workout-plans.show');
    Route::post('/workout-plans/toggle-completion', [App\Http\Controllers\WorkoutPlanController::class, 'toggleCompletion'])->name('workout-plans.toggle-completion');

    // Meal Plan Routes
    Route::get('/meal-plans', [App\Http\Controllers\MealPlanController::class, 'index'])->name('meal-plans.index');
    Route::get('/meal-plans/{mealPlan}', [App\Http\Controllers\MealPlanController::class, 'show'])->name('meal-plans.show');
    Route::post('/meal-plans/toggle-completion', [App\Http\Controllers\MealPlanController::class, 'toggleCompletion'])->name('meal-plans.toggle-completion');
});

require __DIR__.'/auth.php';
