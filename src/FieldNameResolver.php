<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Str;

class FieldNameResolver
{
    protected $globalFields = [];
    protected $type;

    public function __construct($type = null, $globalFields = null)
    {
        $this->globalFields = $globalFields ?: DocumentResolver::getGlobalFieldKeys();
        $this->type = $type;
    }

    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    public function resolve($field)
    {
        if (Str::startsWith($field, ['document', 'my'])) {
            return $field;
        }

        if ($field != 'uid' && in_array($field, $this->globalFields)) {
            return 'document.'.$field;
        }

        if ($this->type && ! Str::startsWith($field, $this->type)) {
            return 'my.'.$this->type.'.'.$field;
        }

        return 'my.'.$field;
    }
}
