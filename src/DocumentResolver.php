<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Collection;

class DocumentResolver
{
    public function resolve($item): ?Document
    {
        if ($document = Document::resolveClassFromType(data_get($item, 'type'))) {
            return $document::isSingle() ? $document::single() : $document::find(data_get($item, 'id'));
        }

        return null;
    }

    public function resolveMany($items): Collection
    {
        $items = collect($items)->filter(function ($item) {
            return data_get($item, 'id');
        });

        $order = $items->pluck('id')->toArray();

        return $items
            ->groupBy('type')
            ->map(function ($group, $type) {
                $ids = $group->pluck('id')->toArray();
                $document = Document::resolveClassFromType($type);

                return $document::findMany($ids);
            })
            ->flatten()
            ->sortBy(function ($document) use ($order) {
                return array_search($document->id, $order);
            })
            ->values();
    }
}
