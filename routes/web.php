<?php

use App\Livewire\Auth\AcceptInvitation;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/accept-invitation/{token}', AcceptInvitation::class)->name('accept-invitation');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/leads', \App\Livewire\Leads\Index::class)->name('leads.index');

    Route::get('/calls', \App\Livewire\Calls\CallsLog::class)->name('calls.index');

    Route::get('/agent/console', \App\Livewire\Agent\Console::class)->name('agent.console');

    Route::post('/agent/console/sip-password', function () {
        $credential = auth()->user()->sipCredential;
        
        if (!$credential) {
            return response()->json(['password' => ''], 404);
        }
        
        return response()->json(['password' => $credential->sip_password]);
    })->name('agent.console.sip-password');

    // Settings routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/sip-credentials', \App\Livewire\Settings\SipCredentials::class)->name('sip-credentials');
        
        // Admin only routes
        Route::middleware('role:tenant_admin')->group(function () {
            Route::get('/lead-statuses', \App\Livewire\Settings\LeadStatuses::class)->name('lead-statuses');
            Route::get('/call-dispositions', \App\Livewire\Settings\CallDispositions::class)->name('call-dispositions');
            Route::get('/users', \App\Livewire\Settings\UsersManagement::class)->name('users');
        });
    });

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
