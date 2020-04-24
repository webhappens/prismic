<?php

namespace WebHappens\Prismic;

use Illuminate\Contracts\Support\Htmlable;

abstract class Slice implements Htmlable
{
    protected static $type;

    protected $viewName;
    protected $_data;

    abstract public function toHtml();

    public static function getType(): string
    {
        return static::$type;
    }

    public static function make(...$parameters): self
    {
        return new static(...$parameters);
    }

    public function __construct($data)
    {
        $this->_data = $data;
    }

    public function using($viewName)
    {
        $this->viewName = $viewName;

        return $this;
    }

    public function viewName()
    {
        return $this->viewName ?: 'slices.'.static::getType();
    }

    protected function data($field, $default = null)
    {
        return data_get($this->_data, $field, $default);
    }
}
