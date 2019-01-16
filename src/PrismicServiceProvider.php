<?php

namespace WebHappens\Prismic;

use Illuminate\Support\ServiceProvider;

class PrismicServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }
    }

    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__ . '/../config/prismic.php' => config_path('prismic.php'),
        ]);
    }

    public function register()
    {
        $this->app->singleton(\Prismic\Api::class, function ($app) {
            $config = $app['config']['prismic'];
            $cache = $config['cache'] ? new $config['cache'] : null;

            return \Prismic\Api::get($config['url'], $config['accessToken'], null, $cache);
        });

        $this->app->bind(\WebHappens\Prismic\Contracts\Fields\RichTextHtmlSerializer::class, \WebHappens\Prismic\Fields\RichTextHtmlSerializer::class);
        $this->app->bind(\WebHappens\Prismic\Contracts\Fields\LinkHtmlSerializer::class, \WebHappens\Prismic\Fields\LinkHtmlSerializer::class);

        $this->mergeConfigFrom(__DIR__ . '/../config/prismic.php', 'prismic');
    }
}
