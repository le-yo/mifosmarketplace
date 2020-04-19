<?php

namespace Modules\MifosInstance\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class MifosInstanceServiceProvider extends ServiceProvider
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
        $this->loadMigrationsFrom(module_path('MifosInstance', 'Database/Migrations'));
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
            module_path('MifosInstance', 'Config/config.php') => config_path('mifosinstance.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('MifosInstance', 'Config/config.php'), 'mifosinstance'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/mifosinstance');

        $sourcePath = module_path('MifosInstance', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/mifosinstance';
        }, \Config::get('view.paths')), [$sourcePath]), 'mifosinstance');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/mifosinstance');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'mifosinstance');
        } else {
            $this->loadTranslationsFrom(module_path('MifosInstance', 'Resources/lang'), 'mifosinstance');
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
            app(Factory::class)->load(module_path('MifosInstance', 'Database/factories'));
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
