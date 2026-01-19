<?php

namespace App\Providers;

use App\Contracts\ActivityLoggerInterface;
use App\Contracts\CallRepositoryInterface;
use App\Contracts\CallServiceInterface;
use App\Contracts\LeadRepositoryInterface;
use App\Contracts\LeadServiceInterface;
use App\Events\CallEnded;
use App\Events\CallStarted;
use App\Events\CallWrappedUp;
use App\Events\LeadAssigned;
use App\Events\LeadCreated;
use App\Events\LeadStatusChanged;
use App\Listeners\LogCallActivity;
use App\Listeners\LogLeadActivity;
use App\Listeners\NotifyAssignedUser;
use App\Listeners\UpdateLeadTimestamps;
use App\Repositories\CallRepository;
use App\Repositories\LeadRepository;
use App\Services\ActivityLoggerService;
use App\Services\CallService;
use App\Services\LeadService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider
 *
 * Registers and bootstraps enterprise-level service bindings,
 * event listeners, and dependency injection containers.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<string, string>
     */
    public array $bindings = [
        LeadServiceInterface::class => LeadService::class,
        CallServiceInterface::class => CallService::class,
        LeadRepositoryInterface::class => LeadRepository::class,
        CallRepositoryInterface::class => CallRepository::class,
    ];

    /**
     * All of the container singletons that should be registered.
     *
     * @var array<string, string>
     */
    public array $singletons = [
        ActivityLoggerInterface::class => ActivityLoggerService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bindings and singletons are automatically registered
        // Additional service registrations can go here
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerEventListeners();
    }

    /**
     * Register event listeners for the application.
     */
    protected function registerEventListeners(): void
    {
        // Lead event listeners
        Event::listen(LeadCreated::class, [LogLeadActivity::class, 'handleLeadCreated']);
        Event::listen(LeadStatusChanged::class, [LogLeadActivity::class, 'handleLeadStatusChanged']);
        Event::listen(LeadAssigned::class, NotifyAssignedUser::class);

        // Call event listeners
        Event::listen(CallStarted::class, [LogCallActivity::class, 'handleCallStarted']);
        Event::listen(CallEnded::class, [LogCallActivity::class, 'handleCallEnded']);
        Event::listen(CallWrappedUp::class, UpdateLeadTimestamps::class);
    }
}
