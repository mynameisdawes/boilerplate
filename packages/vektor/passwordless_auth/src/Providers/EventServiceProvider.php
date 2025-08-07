<?php

namespace Vektor\PasswordlessAuth\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Vektor\PasswordlessAuth\Events\LoginLinkRequested;
use Vektor\PasswordlessAuth\Events\RegistrationLinkRequested;
use Vektor\PasswordlessAuth\Listeners\SendLoginLinkEmail;
use Vektor\PasswordlessAuth\Listeners\SendRegistrationLinkEmail;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        LoginLinkRequested::class => [
            SendLoginLinkEmail::class,
        ],
        RegistrationLinkRequested::class => [
            SendRegistrationLinkEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
