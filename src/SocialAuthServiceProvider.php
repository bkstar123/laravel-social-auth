<?php
/**
 * SocialAuthServiceProvider
 *
 * @author: tuanha
 * @last-mod: 22-06-2019
 */
namespace Bkstar123\SocialAuth;

use Illuminate\Support\ServiceProvider;

class SocialAuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('bkstar123_socialauth.loadMigration')) {
            $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        } else {
            $this->publishes([
                __DIR__.'/Database/Migrations' => database_path('migrations'),
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/Config/services.php', 'services');
        $this->mergeConfigFrom(__DIR__.'/Config/bkstar123_socialauth.php', 'bkstar123_socialauth');
    }
}
