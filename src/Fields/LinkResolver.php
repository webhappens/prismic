<?php

namespace WebHappens\Prismic\Fields;

use WebHappens\Prismic\Query;

class LinkResolver
{
    public function resolve($item, $title = null): ?Link
    {
        $method = 'make'.data_get($item, 'link_type').'Link';

        return method_exists($this, $method) ? $this->{$method}($item, $title) : null;
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
        if (! $document = Query::make()->find(data_get($item, 'id'))) {
            return null;
        }

        return DocumentLink::make($document, $title);
    }
}
