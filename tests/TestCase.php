<?php

namespace WebHappens\Prismic\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use WebHappens\Prismic\PrismicServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [PrismicServiceProvider::class];
    }
}
