<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffLabController;
use App\Http\Controllers\KepalaLabController;
use App\Http\Controllers\StaffAdminController;
use App\Http\Controllers\KaprodiController;
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

    Route::middleware('role:kepala_lab')->prefix('kepala-lab')->name('kepala_lab.')->group(function () {
        Route::get('/dashboard', [KepalaLabController::class, 'dashboard'])->name('dashboard');
        Route::post('/drafts', [KepalaLabController::class, 'storeDraft'])->name('drafts.store');
        Route::post('/drafts/{draft}/items', [KepalaLabController::class, 'addDraftItem'])->name('drafts.items.store');
        Route::delete('/draft-items/{item}', [KepalaLabController::class, 'deleteDraftItem'])->name('draft-items.destroy');
        Route::delete('/drafts/{draft}', [KepalaLabController::class, 'deleteDraft'])->name('drafts.destroy');
    });

    Route::middleware('role:kaprodi')->prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::get('/dashboard', [KaprodiController::class, 'dashboard'])->name('dashboard');
        Route::patch('/items/{item}/approve', [KaprodiController::class, 'approveItem'])->name('items.approve');
        Route::patch('/items/{item}/reject', [KaprodiController::class, 'rejectItem'])->name('items.reject');
        Route::patch('/drafts/{draft}/finalize', [KaprodiController::class, 'finalizeDraft'])->name('drafts.finalize');
    });

    Route::middleware('role:staff_admin')->prefix('staff-admin')->name('staff_admin.')->group(function () {
        Route::get('/dashboard', [StaffAdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/purchase-orders/{draftItem}', [StaffAdminController::class, 'createPurchaseOrder'])->name('po.store');
        Route::post('/goods-receipts/{purchaseOrder}', [StaffAdminController::class, 'recordGoodsReceipt'])->name('goods-receipt.store');
        Route::post('/inventory', [StaffAdminController::class, 'registerInventory'])->name('inventory.store');
    });

    Route::middleware('role:staff_lab')->prefix('staff-lab')->name('staff_lab.')->group(function () {
        Route::get('/dashboard', [StaffLabController::class, 'dashboard'])->name('dashboard');
        Route::patch('/bhp/{bhpItem}/stock', [StaffLabController::class, 'updateBhpStock'])->name('bhp.update-stock');
        Route::post('/bhp', [StaffLabController::class, 'storeBhp'])->name('bhp.store');
        Route::post('/maintenance-logs', [StaffLabController::class, 'storeMaintenanceLog'])->name('maintenance.store');
    });
});

require __DIR__.'/auth.php';
