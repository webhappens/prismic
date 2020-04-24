<?php

namespace WebHappens\Prismic;

use InvalidArgumentException;

trait HasOrderings
{
    protected $orderings = [];

    public function orderBy(string $field, $direction = 'asc'): Query
    {
        if ( ! in_array($direction, ['asc', 'desc'])) {
            throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
        }

        $field = $this->resolveFieldName($field);
        $direction = $direction == 'desc' ? ' desc' : '';

        array_push($this->orderings, $field.$direction);

        return $this;
    }

    public function orderByDesc(string $field)
    {
        return $this->orderBy($field, 'desc');
    }
}
