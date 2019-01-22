<?php

namespace WebHappens\Prismic;

use stdClass;
use ArrayAccess;
use Illuminate\Support\Collection;
use WebHappens\Prismic\Fields\Date;
use WebHappens\Prismic\HasHierarchy;
use WebHappens\Prismic\HasAttributes;
use WebHappens\Prismic\Fields\RichText;
use WebHappens\Prismic\DocumentResolver;
use Illuminate\Support\Traits\ForwardsCalls;

abstract class Document implements ArrayAccess
{
    use HasAttributes,
        HasHierarchy,
        ForwardsCalls;

    protected static $type;
    protected static $isSingle = false;

    private $_data;

    public static function resolve(...$parameters): ?Document
    {
        return resolve(DocumentResolver::class)->resolve(...$parameters);
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

    public static function isSingle(): bool
    {
        return static::$isSingle;
    }

    public static function all(): Collection
    {
        return static::make()->newQuery()->get();
    }

    public function isLinkable()
    {
        return isset($this->url, $this->title);
    }

    public function getFirstPublishedAttribute($value)
    {
        return Date::make($value);
    }

    public function getLastPublishedAttribute($value)
    {
        return Date::make($value);
    }

    public function getSlices($types = []): Collection
    {
        $types = array_wrap($types);
        $slices = collect($this->data('body', []));

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

    public function data($key = null, $default = null)
    {
        if (func_num_args() === 0) {
            return $this->_data;
        }

        return data_get($this->_data, $key, $default);
    }

    public function setData($data)
    {
        $this->_data = $data;

        return $this;
    }

    public function hydrateAttributes(stdClass $result)
    {
        $this->attributes['id'] = data_get($result, 'id');
        $this->attributes['apiUrl'] = data_get($result, 'href');
        $this->attributes['firstPublished'] = data_get($result, 'first_publication_date');
        $this->attributes['lastPublished'] = data_get($result, 'last_publication_date');
        $this->attributes['language'] = data_get($result, 'lang');

        foreach ($this->attributeMap as $attribute => $key) {
            $this->attributes[$attribute] = data_get($result, 'data.' . $key);
        }

        return $this;
    }

    public function newFromResponseResult(stdClass $result): Document
    {
        return static::make()
            ->setData(data_get($result, 'data'))
            ->hydrateAttributes($result);
    }

    public function newQuery(): Query
    {
        return (new Query)
            ->setDocument($this)
            ->where('document.type', $this->getType());
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
            case "richtext_string":
                return (string) RichText::make($value);
            case "url":
                return url($value);
        }

        return $value;
    }
}
