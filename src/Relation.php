<?php

namespace WebHappens\Prismic;

class Relation
{
    public function one($field)
    {
        $id = data_get($field, 'id');
        $isBroken = data_get($field, 'isBroken');

        if ( ! $id || $isBroken) {
            return;
        }

        return $this->query()->lang('*')->find($id);
    }

    public function many($data, $key)
    {
        if (empty($data)) {
            return collect();
        }

        return $this->query()->lang('*')->findMany(
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
