<?php

namespace WebHappens\Prismic;

use ArrayAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use WebHappens\Prismic\Fields\Date;
use WebHappens\Prismic\Fields\RichText;

abstract class Document implements ArrayAccess
{
    use HasAttributes,
        ForwardsCalls;

    protected static $type;

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

    public static function make(): self
    {
        return resolve(static::class);
    }

    public static function getType(): string
    {
        return static::$type;
    }

    public static function all(): Collection
    {
        return static::make()->newQuery()->get();
    }

    public function isLinkable(): bool
    {
        return isset($this->url, $this->title);
    }

    public function getSlicesFor($sliceZone = 'body', $types = []): Collection
    {
        $types = is_array($types) ? $types : func_get_args();
        $slices = collect($this->$sliceZone ?? []);

        if (count($types)) {
            $slices = $slices->filter(function ($data) use ($types) {
                    return in_array(data_get($data, 'slice_type'), $types);
                })
                ->values();
        }

        return $slices
            ->map(function ($data) use ($sliceZone) {
                return Prismic::sliceResolver(static::$type, $sliceZone, data_get($data, 'slice_type'), $data);
            })
            ->filter();
    }

    public function getSlices($types = []): Collection
    {
        return $this->getSlicesFor('body', $types);
    }

    public function hydrate(array $result)
    {
        $maps = $this->getMaps();

        foreach ($result as $key => $value) {
            if (array_key_exists($key, $maps)) {
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
            case 'date':
                return Date::make($value);
            case 'richtext':
                return RichText::make($value);
            case 'url':
                return url($value);
        }

        return $value;
    }
}
