<?php

namespace WebHappens\Prismic;

use Prismic\LinkResolver;

class DocumentUrlResolver extends LinkResolver
{
    public function resolve($data): ?string
    {
        if ($document = Document::resolveClassFromType(data_get($data, 'type'))) {
            $document = $document::make();

            if ($document->isLinkable()) {
                return $document->url;
            }
        }

        return url('/');
    }
}
