<?php

namespace WebHappens\Prismic;

class Relation
{
    public function one($field)
    {
        if ($field->isBroken) {
            return;
        }

        return $this->query()->find($field->id);
    }

    public function many($data, $key)
    {
        if (empty($data)) {
            return collect();
        }

        return $this->query()->findMany(
            collect($data)
                ->reject(function ($item) use ($key) {
                    return data_get($item, "{$key}.isBroken");
                })
                ->pluck("{$key}.id")
                ->filter()
                ->toArray()
        );
    }

    protected function query()
    {
        return Query::make();
    }
}
