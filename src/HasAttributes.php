<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasAttributes
{
    protected $attributes = [];
    protected $casts = [];

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($key)
    {
        if ( ! $key) {
            return;
        }

        $key = Str::snake($key);

        if (array_key_exists($key, $this->attributes) || $this->hasAttributeAccessor($key)) {
            return $this->getAttributeValue($key);
        }
    }

    public function getAttributeValue($key)
    {
        $value = Arr::get($this->attributes, $key);

        if ($this->hasAttributeAccessor($key)) {
            return $this->callAttributeAccessor($key, $value);
        }

        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        return $value;
    }

    public function hasAttributeAccessor($key)
    {
        return method_exists($this, 'get'.Str::studly($key).'Attribute');
    }

    protected function callAttributeAccessor($key, $value)
    {
        return $this->{'get'.Str::studly($key).'Attribute'}($value);
    }

    public function setAttribute($key, $value)
    {
        $key = Str::snake($key);

        if ($this->hasAttributeMutator($key)) {
            return $this->callAttributeMutator($key, $value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    public function hasAttributeMutator($key)
    {
        return method_exists($this, 'set'.Str::studly($key).'Attribute');
    }

    protected function callAttributeMutator($key, $value)
    {
        return $this->{'set'.Str::studly($key).'Attribute'}($value);
    }

    public function getCasts()
    {
        return $this->casts;
    }

    public function hasCast($key)
    {
        return array_key_exists($key, $this->getCasts());
    }

    protected function castAttribute($key, $value)
    {
        if ( ! empty($value) && method_exists($this, 'customCastAttribute')) {
            $value = $this->customCastAttribute($this->getCastType($key), $value);
        }

        return $value;
    }

    protected function getCastType($key)
    {
        return trim(strtolower($this->getCasts()[$key]));
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function offsetExists($offset)
    {
        return ! is_null($this->getAttribute($offset));
    }

    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->attributes[Str::snake($offset)]);
    }

    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    public function __unset($key)
    {
        $this->offsetUnset($key);
    }
}
