<?php

namespace WebHappens\Prismic\Fields;

use Illuminate\Support\Collection;
use WebHappens\Prismic\DocumentResolver;

class LinkResolver
{
    public function resolve($item, $title = null): ?Link
    {
        $method = 'make' . data_get($item, 'link_type') . 'Link';

        return method_exists($this, $method) ? $this->{$method}($item, $title) : null;
    }

    public function resolveMany($items): Collection
    {
        $items = collect($items)
            ->reject(function ($item) {
                $linkType = data_get($item, '0.link_type');
                $id = data_get($item, '0.id');

                return (bool) ($linkType == 'Document' && ! $id) || $linkType == 'Any';
            })
            ->map(function ($item, $key) {
                $item['order'] = $key;

                return $item;
            });

        $order = $items->pluck('order')->toArray();
        $items = $items->groupBy('0.link_type');
        $links = collect();

        if (isset($items['Document'])) {
            $links = $links->merge(
                $this->makeDocumentLinks($items['Document'])
            );

            unset($items['Document']);
        }

        $items = $items->collapse();

        foreach ($items as $key => $item) {
            $link = $this->resolve($item[0], $item[1]);
            $link->order = $item['order'];
            $links->push($link);
        }

        $links = $links
            ->sortBy(function ($link) use ($order) {
                return array_search($link->order, $order);
            })
            ->map(function ($link) {
                unset($link->order);

                return $link;
            })
            ->values();

        return $links;
    }

    protected function makeWebLink($item, $title)
    {
        return WebLink::make(data_get($item, 'url'), $title)
            ->openInNewTab(
                (bool) data_get($item, 'target') == '_blank'
            );
    }

    protected function makeMediaLink($item, $title)
    {
        return MediaLink::make(data_get($item, 'url'), $title, $item);
    }

    protected function makeDocumentLink($item, $title)
    {
        if ( ! $document = resolve(DocumentResolver::class)->resolve($item)) {
            return null;
        }

        return DocumentLink::make($document, $title);
    }

    protected function makeDocumentLinks($items)
    {
        $titles = $items->pluck(1, '0.id');
        $order = $items->pluck('order', '0.id');

        return resolve(DocumentResolver::class)
            ->resolveMany($items->pluck(0))
            ->map(function ($document) use ($titles, $order) {
                $link = DocumentLink::make($document, data_get($titles, $document->id));
                $link->order = data_get($order, $document->id);

                return $link;
            });
    }
}
