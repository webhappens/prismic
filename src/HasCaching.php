<?php

namespace WebHappens\Prismic;

use WebHappens\Prismic\Query;
use WebHappens\Prismic\Document;
use Illuminate\Support\Collection;

trait HasCaching
{
    protected $shouldCache = false;
    protected static $allDocumentsCached = false;
    protected static $cachedDocuments = [];

    public static function eagerLoadAll(): Query
    {
        if ( ! static::$allDocumentsCached) {
            static::make()->cache()->get();
            static::$allDocumentsCached = true;
        }

        return static::make();
    }

    public static function setDocumentCache(Collection $records): Collection
    {
        return static::$cachedDocuments = $records->whereInstanceOf(Document::class)->keyBy('id');
    }

    public static function clearDocumentCache(): Collection
    {
        return static::$cachedDocuments = collect();
    }

    public static function addToDocumentCache(Collection $documents): Collection
    {
        $documents = $documents->whereInstanceOf(Document::class)->keyBy('id');
        static::setDocumentCache(static::documentCache()->merge($documents));

        return $documents;
    }

    public static function documentCache()
    {
        return collect(static::$cachedDocuments)->filter();
    }

    public function cache(): Query
    {
        $this->shouldCache = true;

        return $this;
    }

    public function dontCache(): Query
    {
        $this->shouldCache = false;

        return $this;
    }
}
