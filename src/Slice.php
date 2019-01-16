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

    public static function make(...$args): Slice
    {
        return new static(...$args);
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
