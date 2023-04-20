<?php

namespace App\Providers;

// next line added for hosting 
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->bind('path.public',function(){
        //     return base_path("public_html");
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // that if block added for hosting 
        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }
    }
}
