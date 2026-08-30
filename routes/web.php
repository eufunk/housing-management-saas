<?php

use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('properties', PropertyController::class)->except('show');
    Route::resource('properties/buildings', BuildingController::class)->except('show')->names('buildings');
    Route::resource('properties/units', UnitController::class)->except('show')->names('units');
    Route::resource('tenants', TenantController::class)->except('show');
    Route::resource('owners', OwnerController::class)->except('show');
    Route::resource('contractors', ContractorController::class)->except('show');

    // Placeholder landing pages for the remaining sidebar modules. These
    // render an EmptyState until the corresponding feature is implemented;
    // wiring real data replaces the Inertia::render calls without touching
    // the routes.
    Route::inertia('leases', 'Leases/Index')->name('leases.index');
    Route::inertia('payments', 'Payments/Index')->name('payments.index');
    Route::inertia('payments/invoices', 'Invoices/Index')->name('invoices.index');
    Route::inertia('payments/expenses', 'Expenses/Index')->name('expenses.index');
    Route::inertia('maintenance', 'Maintenance/Index')->name('maintenance.index');
    Route::inertia('documents', 'Documents/Index')->name('documents.index');
    Route::inertia('appointments', 'Appointments/Index')->name('appointments.index');
    Route::inertia('notifications', 'Notifications/Index')->name('notifications.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
