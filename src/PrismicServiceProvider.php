<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PrismicServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

        $this->registerRoutes();
    }

    public function register()
    {
        $this->app->singleton(\Prismic\Api::class, function ($app) {
            $config = $app['config']['prismic'];
            $cache = $config['cache'] ? new $config['cache'] : null;

            return \Prismic\Api::get($config['url'], $config['access_token'], null, $cache);
        });

        $this->app->singleton(\WebHappens\Prismic\DocumentResolver::class);
        $this->app->singleton(\WebHappens\Prismic\SliceResolverCollection::class);

        $this->app->bind(\WebHappens\Prismic\Contracts\Fields\RichTextHtmlSerializer::class, \WebHappens\Prismic\Fields\RichTextHtmlSerializer::class);
        $this->app->bind(\WebHappens\Prismic\Contracts\Fields\LinkHtmlSerializer::class, \WebHappens\Prismic\Fields\LinkHtmlSerializer::class);
        $this->app->bind(\WebHappens\Prismic\Contracts\Fields\DateHtmlSerializer::class, \WebHappens\Prismic\Fields\DateHtmlSerializer::class);

        $this->mergeConfigFrom(__DIR__.'/../config/prismic.php', 'prismic');
    }

    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__.'/../config/prismic.php' => config_path('prismic.php'),
        ]);
    }

    protected function registerRoutes()
    {
        Route::post('/prismic-webhook', '\\WebHappens\\Prismic\\Http\\Controllers\\WebhookController');
    }
}
