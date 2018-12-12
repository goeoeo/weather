<?php
/**
 * Auth:chenyu.
 * Mail:phpdi@sina.com
 * Date: 18-12-12
 * Desc:
 */

namespace Phpdi\Weather;


class ServiceProvider extends  \Illuminate\Support\ServiceProvider
{
    protected $defer=true;

    public function register()
    {
        $this->app->singleton(Weather::class, function () {
            return new Weather(config('services.weather.key'));
        });

        $this->app->alias(Weather::class, 'weather');
    }

    public function provides()
    {
        return [Weather::class, 'weather'];
    }

}