<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user) {
        return redirect()->route($user->role . '.dashboard');
    }
    return redirect('/login');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Role-based dashboard routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [\App\Http\Controllers\DashboardController::class, 'admin'])->name('admin.dashboard');
        Route::resource('/admin/users', \App\Http\Controllers\UserController::class);
        Route::resource('/admin/rooms', \App\Http\Controllers\RoomController::class);
    });

    Route::middleware('role:kepala_lab')->group(function () {
        Route::get('/kepala-lab/dashboard', [\App\Http\Controllers\DashboardController::class, 'kepalaLab'])->name('kepala_lab.dashboard');
    });

    Route::middleware('role:kaprodi')->group(function () {
        Route::get('/kaprodi/dashboard', [\App\Http\Controllers\DashboardController::class, 'kaprodi'])->name('kaprodi.dashboard');
    });

    Route::middleware('role:staff_admin')->group(function () {
        Route::get('/staff-admin/dashboard', [\App\Http\Controllers\DashboardController::class, 'staffAdmin'])->name('staff_admin.dashboard');
    });

    Route::middleware('role:staff_lab')->group(function () {
        Route::get('/staff-lab/dashboard', [\App\Http\Controllers\DashboardController::class, 'staffLab'])->name('staff_lab.dashboard');
    });
});

require __DIR__.'/auth.php';
