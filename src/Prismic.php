<?php

namespace WebHappens\Prismic;

use Prismic\Api;
use BadMethodCallException;
use Illuminate\Http\RedirectResponse;
use WebHappens\Prismic\DocumentUrlResolver;

class Prismic
{
    public static $documents = [];
    public static $slices = [];

    public static function documents(array $documents): Prismic
    {
        static::$documents = array_merge(static::$documents, $documents);

        return new static;
    }

    public static function slices(array $slices): Prismic
    {
        static::$slices = array_merge(static::$slices, $slices);

        return new static;
    }

    public static function preview($token): RedirectResponse
    {
        $url = resolve(Api::class)->previewSession($token, resolve(DocumentUrlResolver::class), '/');

        return redirect($url);
    }
}
