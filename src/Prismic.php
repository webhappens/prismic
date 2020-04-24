<?php

namespace WebHappens\Prismic;

use Illuminate\Http\RedirectResponse;
use Prismic\Api;
use WebHappens\Prismic\DocumentUrlResolver;

class Prismic
{
    public static $documents = [];
    public static $slices = [];

    public static function documents(array $documents): self
    {
        static::$documents = array_merge(static::$documents, $documents);

        return new static;
    }

    public static function slices(array $slices): self
    {
        static::$slices = array_merge(static::$slices, $slices);

        return new static;
    }

    public static function documentResolver(...$args)
    {
        if ($args) {
            return resolve(DocumentResolver::class)->resolve(...$args);
        }

        return resolve(DocumentResolver::class);
    }

    public static function sliceResolver(...$args)
    {
        if ($args) {
            return resolve(SliceResolver::class)->resolve(...$args);
        }

        return resolve(SliceResolver::class);
    }

    public static function preview($token): RedirectResponse
    {
        $url = resolve(Api::class)->previewSession($token, resolve(DocumentUrlResolver::class), '/');

        return redirect($url);
    }
}
