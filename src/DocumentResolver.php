<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Collection;

class DocumentResolver
{
    public function resolve($item): ?Document
    {
        return (new Query)->find(data_get($item, 'id'));
    }

    public function resolveMany($items): Collection
    {
        $ids = collect($items)->pluck('id')->filter()->toArray();

        return (new Query)->findMany($ids);
    }
}
