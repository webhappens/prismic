<?php

namespace WebHappens\Prismic;

use Illuminate\Http\RedirectResponse;
use Prismic\Api;

class Prismic
{
    public static $documents = [];

    public static function documents(array $documents): self
    {
        static::$documents = array_merge(static::$documents, $documents);

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
            return resolve(SliceResolverCollection::class)->resolve(...$args);
        }

        return resolve(SliceResolverCollection::class);
    }

    public static function preview($token): RedirectResponse
    {
        $url = resolve(Api::class)->previewSession($token, resolve(DocumentUrlResolver::class), '/');

        return redirect($url);
    }
}
