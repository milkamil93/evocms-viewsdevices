<?php namespace EvolutionCMS\ViewsDevices;

use EvolutionCMS\ServiceProvider;

class ViewsDevicesServiceProvider extends ServiceProvider
{
    protected $namespace = 'ViewsDevices';

    public function register()
    {
        $this->commands([
            'EvolutionCMS\ViewsDevices\Console\ViewsDevicesTemplateCommand'
        ]);

        $this->app->singleton('TemplateProcessor', function ($app) {
            return new ViewsDevicesTemplateProcessor($app);
        });
    }
}
