<?php

return [

    'url' => env('PRISMIC_URL'),
    'access_token' => env('PRISMIC_ACCESS_TOKEN'),
    'webhook_secret' => env('PRISMIC_WEBHOOK_SECRET'),
    'cache' => \WebHappens\Prismic\Cache::class,

];
