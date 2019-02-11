<?php

namespace WebHappens\Prismic;

use Prismic\LinkResolver;

class DocumentUrlResolver extends LinkResolver
{
    public function resolve($data): ?string
    {
        if ($document = Document::resolveClassFromType(data_get($data, 'type'))) {
            $document = $document::newHydratedInstance($data);

            if ($document->isLinkable()) {
                return $document->url;
            }
        }

        return url('/');
    }
}
