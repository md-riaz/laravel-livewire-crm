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

    Route::get('/calls', \App\Livewire\Calls\CallsLog::class)->name('calls.index');

    Route::get('/agent/console', \App\Livewire\Agent\Console::class)->name('agent.console');

    Route::post('/agent/console/sip-password', function () {
        $credential = auth()->user()->sipCredential;
        
        if (!$credential) {
            return response()->json(['password' => ''], 404);
        }
        
        return response()->json(['password' => $credential->sip_password]);
    })->name('agent.console.sip-password');

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
