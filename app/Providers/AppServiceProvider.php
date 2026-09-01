<?php

namespace App\Providers;

use App\Contracts\TmdbPort;
use App\Listeners\ProcessTusUploadCompleted;
use App\Services\TmdbClient;
use ArthurPatriot\Tus\Events\FileUploadFinished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TmdbPort::class, TmdbClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            FileUploadFinished::class,
            ProcessTusUploadCompleted::class,
        );
    }
}
