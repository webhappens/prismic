<?php

namespace WebHappens\Prismic\Fields;

use WebHappens\Prismic\DocumentResolver;

class LinkResolver
{
    public function resolve($data, $title = null): ?Link
    {
        switch (data_get($data, 'link_type')) {
            case "Web":
                return WebLink::make(data_get($data, 'url'), $title);

            case "Media":
                return MediaLink::make(data_get($data, 'url'), $title, $data);
        }

        if ($document = resolve(DocumentResolver::class)->resolve($data)) {
            return DocumentLink::make($document, $title);
        }

        return null;
    }
}
