<?php

namespace Modules\HelaplusTokens\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class HelaplusTokensServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path('HelaplusTokens', 'Database/Migrations'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('HelaplusTokens', 'Config/config.php') => config_path('helaplustokens.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('HelaplusTokens', 'Config/config.php'), 'helaplustokens'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/helaplustokens');

        $sourcePath = module_path('HelaplusTokens', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/helaplustokens';
        }, \Config::get('view.paths')), [$sourcePath]), 'helaplustokens');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/helaplustokens');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'helaplustokens');
        } else {
            $this->loadTranslationsFrom(module_path('HelaplusTokens', 'Resources/lang'), 'helaplustokens');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path('HelaplusTokens', 'Database/factories'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
