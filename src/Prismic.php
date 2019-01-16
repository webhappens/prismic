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

    public static function findDocumentByType($type): ?string
    {
        foreach (static::$documents as $document) {
            if ($document::getType() == $type) {
                return $document;
            }
        }

        return null;
    }

    public static function slices(array $slices): Prismic
    {
        static::$slices = array_merge(static::$slices, $slices);

        return new static;
    }

    public static function findSliceByType($type): ?string
    {
        foreach (static::$slices as $slice) {
            if ($slice::getType() == $type) {
                return $slice;
            }
        }

        return null;
    }

    public static function preview($token): RedirectResponse
    {
        $url = resolve(Api::class)->previewSession($token, resolve(DocumentUrlResolver::class), '/');

        return redirect($url);
    }

    public static function __callStatic($method, $parameters)
    {
        if ( ! property_exists(get_called_class(), $method)) {
            throw new BadMethodCallException("Method {$method} does not exist.");
        }

        return static::${$method};
    }
}
