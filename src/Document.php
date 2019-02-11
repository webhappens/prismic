<?php

namespace WebHappens\Prismic;

use stdClass;
use ArrayAccess;
use Illuminate\Support\Collection;
use WebHappens\Prismic\Fields\Date;
use WebHappens\Prismic\Fields\RichText;
use Illuminate\Support\Traits\ForwardsCalls;

abstract class Document implements ArrayAccess
{
    use HasAttributes,
        ForwardsCalls,
        Traversable;

    protected static $type;

    protected static $isSingle = false;

    protected $globalFieldKeys = [
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

    public static function resolve(...$parameters): ?Document
    {
        return resolve(DocumentResolver::class)->resolve(...$parameters);
    }

    public static function resolveMany($items): Collection
    {
        return resolve(DocumentResolver::class)->resolveMany($items);
    }

    public static function make(): Document
    {
        return new static;
    }

    public static function getType(): string
    {
        return static::$type;
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

    public static function newHydratedInstance(stdClass $result) : Document
    {
        return static::make()->hydrate($result);
    }

    public static function isSingle(): bool
    {
        return static::$isSingle;
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
        $types = array_wrap($types);
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

        foreach ($this->globalFieldKeys as $key) {
            $attributes[$key] = $result->{$key};
        }

        foreach ($result->data as $key => $value) {
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
        return (new Query)
            ->setDocument($this)
            ->where('type', $this->getType());
    }

    public function getGlobalFieldKeys()
    {
        return $this->globalFieldKeys;
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
