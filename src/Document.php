<?php

namespace WebHappens\Prismic;

use stdClass;
use ArrayAccess;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use WebHappens\Prismic\Fields\Date;
use WebHappens\Prismic\Fields\RichText;
use Illuminate\Support\Traits\ForwardsCalls;

abstract class Document implements ArrayAccess
{
    use HasAttributes,
        ForwardsCalls;

    protected static $type;

    protected static $globalFieldKeys = [
        'id', 'uid', 'type', 'href', 'tags', 'first_publication_date',
        'last_publication_date', 'lang', 'alternate_languages',
    ];

    protected $globalMaps = [
        'href' => 'api_id',
        'first_publication_date' => 'first_published',
        'last_publication_date' => 'last_published',
        'lang' => 'language',
    ];

    protected $globalCasts = [
        'first_published' => 'date',
        'last_published' => 'date',
    ];

    protected $maps = [];

    public static function make(): Document
    {
        return new static;
    }

    public static function getType(): string
    {
        return static::$type;
    }

    public static function getGlobalFieldKeys(): array
    {
        return static::$globalFieldKeys;
    }

    public static function resolveClassFromType($type): ?string
    {
        foreach (Prismic::$documents as $document) {
            if ($document::getType() == $type) {
                return $document;
            }
        }

        return null;
    }

    public static function newHydratedInstance(stdClass $result): ?Document
    {
        if ( ! $document = Document::resolveClassFromType(data_get($result, 'type'))) {
            return null;
        }

        return $document::make()->hydrate($result);
    }

    public static function all(): Collection
    {
        return static::make()->newQuery()->get();
    }

    public function isLinkable(): bool
    {
        return isset($this->url, $this->title);
    }

    public function getSlices($types = []): Collection
    {
        $types = Arr::wrap($types);
        $slices = collect($this->body ?? []);

        if (count($types)) {
            $slices = $slices->filter(function ($data) use ($types) {
                return in_array(data_get($data, 'slice_type'), $types);
            });
        }

        return $slices
            ->map(function ($data) {
                if ($slice = Slice::resolveClassFromType(data_get($data, 'slice_type'))) {
                    return $slice::make($data);
                }
            })
            ->filter();
    }

    public function hydrate(stdClass $result)
    {
        $attributes = [];

        foreach (static::getGlobalFieldKeys() as $key) {
            $attributes[$key] = data_get($result, $key);
        }

        $data = data_get($result, 'data', []);

        foreach ($data as $key => $value) {
            $attributes[$key] = $value;
        }

        $maps = $this->getMaps();

        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $maps)) {
                unset($attributes[$key]);
                $key = $maps[$key];
            }

            $this->{$key} = $value;
        }

        return $this;
    }

    public function newQuery(): Query
    {
        return Query::make()->type($this->getType());
    }

    public function getMaps()
    {
        return array_merge($this->globalMaps, $this->maps);
    }

    public function getCasts()
    {
        return array_merge($this->globalCasts, $this->casts);
    }

    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->newQuery(), $method, $parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return static::make()->$method(...$parameters);
    }

    protected function customCastAttribute($type, $value)
    {
        switch ($type) {
            case "date":
                return Date::make($value);
            case "richtext":
                return RichText::make($value);
            case "url":
                return url($value);
        }

        return $value;
    }
}
