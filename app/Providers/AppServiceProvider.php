<?php

namespace App\Providers;

use App\Services\Sms\AfricaTalkingProvider;
use App\Services\Sms\SmsProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
public function register(): void
{
    $this->app->singleton(SmsProvider::class, function () {
    return match (config('sms.default')) {
        'africastalking' => new AfricaTalkingProvider(),
        default          => new AfricaTalkingProvider(),
    };
});
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
