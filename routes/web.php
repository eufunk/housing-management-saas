<?php

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

    // Placeholder landing pages for the main sidebar modules. These render an
    // EmptyState until the corresponding feature is implemented; wiring real
    // data replaces the Inertia::render calls without touching the routes.
    Route::inertia('properties', 'Properties/Index')->name('properties.index');
    Route::inertia('properties/buildings', 'Buildings/Index')->name('buildings.index');
    Route::inertia('properties/units', 'Units/Index')->name('units.index');
    Route::inertia('tenants', 'Tenants/Index')->name('tenants.index');
    Route::inertia('owners', 'Owners/Index')->name('owners.index');
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
