<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Collection;

class DocumentResolver
{
    public function resolve($item): ?Document
    {
        return Query::make()->find(data_get($item, 'id'));
    }

    public function resolveMany($items): Collection
    {
        $ids = collect($items)->pluck('id')->filter()->toArray();

        return Query::make()->findMany($ids);
    }
}
