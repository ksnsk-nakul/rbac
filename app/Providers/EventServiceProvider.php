<?php

namespace App\Providers;

use App\Listeners\LogLoginActivity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [LogLoginActivity::class],
        Logout::class => [LogLoginActivity::class],
        Failed::class => [LogLoginActivity::class],
    ];
}
