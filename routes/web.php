<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::view('/', 'home');

Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');

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
    ->middleware(['auth', 'verified', 'assessment.completed'])
    ->name('dashboard');

Route::middleware(['auth', 'assessment.completed'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Unified Daily Schedule & Tracking Hub
    Route::get('/schedule', [App\Http\Controllers\ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule/items/{item}/toggle', [App\Http\Controllers\ScheduleController::class, 'toggleItem'])->name('schedule.items.toggle');
    Route::post('/schedule/items/{item}/workout-sets', [App\Http\Controllers\ScheduleController::class, 'saveWorkoutSets'])->name('schedule.items.workout-sets');
    Route::post('/schedule/items/{item}/meal-consumption', [App\Http\Controllers\ScheduleController::class, 'saveMealConsumption'])->name('schedule.items.meal-consumption');

    // Auto-Generate Plans Route
    Route::post('/plans/generate', [App\Http\Controllers\PlanGenerationController::class, 'generate'])->name('plans.generate');

    // InBody Tracking Routes
    Route::get('/inbody', [App\Http\Controllers\InBodyLogController::class, 'index'])->name('inbody.index');
    Route::post('/inbody', [App\Http\Controllers\InBodyLogController::class, 'store'])->name('inbody.store');
    Route::get('/inbody/analysis', [App\Http\Controllers\InBodyLogController::class, 'analysis'])->name('inbody.analysis');
    Route::get('/inbody/{inbody}', [App\Http\Controllers\InBodyLogController::class, 'show'])->name('inbody.show');
    Route::put('/inbody/{inbody}', [App\Http\Controllers\InBodyLogController::class, 'update'])->name('inbody.update');
    Route::delete('/inbody/{inbody}', [App\Http\Controllers\InBodyLogController::class, 'destroy'])->name('inbody.destroy');

    // Analytics & Progression Routes
    Route::get('/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/workout/{workout}', [App\Http\Controllers\AnalyticsController::class, 'workoutAnalytics'])->name('analytics.workout');
    Route::get('/analytics/workout/{workout}/pbs', [App\Http\Controllers\AnalyticsController::class, 'personalBests'])->name('analytics.personal-bests');
    Route::get('/analytics/muscles', [App\Http\Controllers\AnalyticsController::class, 'muscleDistribution'])->name('analytics.muscles');

    // Fitness Score Routes
    Route::get('/fitness-score', [App\Http\Controllers\FitnessScoreController::class, 'current'])->name('fitness-score.current');
    Route::get('/fitness-score/history', [App\Http\Controllers\FitnessScoreController::class, 'history'])->name('fitness-score.history');
    Route::post('/fitness-score/recalculate', [App\Http\Controllers\FitnessScoreController::class, 'recalculate'])->name('fitness-score.recalculate');

    // Water Intake Routes
    Route::get('/water', [App\Http\Controllers\WaterIntakeController::class, 'index'])->name('water.index');
    Route::post('/water/log', [App\Http\Controllers\WaterIntakeController::class, 'log'])->name('water.log');
    Route::delete('/water/entries/{id}', [App\Http\Controllers\WaterIntakeController::class, 'deleteEntry'])->name('water.delete');
    Route::post('/water/target', [App\Http\Controllers\WaterIntakeController::class, 'setTarget'])->name('water.target');

    // Notification Routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::patch('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
});

Route::middleware('auth')->group(function () {
    Route::get('/assessment', [App\Http\Controllers\AssessmentController::class, 'index'])->name('assessment.index');
    Route::post('/assessment', [App\Http\Controllers\AssessmentController::class, 'store'])->name('assessment.store');

    // Subscription Routes
    Route::get('/packages', [App\Http\Controllers\SubscriptionController::class, 'packages'])->name('packages.index');
    Route::post('/subscriptions/initiate', [App\Http\Controllers\SubscriptionController::class, 'initiate'])->name('subscriptions.initiate');
    Route::get('/subscriptions/callback', [App\Http\Controllers\SubscriptionController::class, 'callback'])->name('subscriptions.callback');
});

require __DIR__.'/auth.php';
