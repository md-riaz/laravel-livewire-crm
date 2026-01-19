<?php

use App\Livewire\Auth\CompanyRegistration;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/login', Login::class)->name('login');
    Route::get('/register-company', CompanyRegistration::class)->name('register-company');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/leads', \App\Livewire\Leads\Kanban::class)->name('leads.index');

    Route::get('/calls', function () {
        return view('calls.index');
    })->name('calls.index');

    Route::get('/agent/console', function () {
        return view('agent.console');
    })->name('agent.console');

    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
