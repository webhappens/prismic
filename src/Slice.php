<?php

namespace WebHappens\Prismic;

use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Htmlable;

abstract class Slice implements Htmlable
{
    protected static $type;

    protected $_data;

    public static function getType(): string
    {
        return static::$type;
    }

    public static function resolveClassFromType($type): ?string
    {
        foreach (Prismic::$slices as $slice) {
            if ($slice::getType() == $type) {
                return $slice;
            }
        }

        return null;
    }

    public static function make(...$parameters): Slice
    {
        return new static(...$parameters);
    }

    public function __construct($data)
    {
        $this->_data = $data;
    }

    abstract public function toHtml(): HtmlString;

    protected function data($field, $default = null)
    {
        return data_get($this->_data, $field, $default);
    }
}
