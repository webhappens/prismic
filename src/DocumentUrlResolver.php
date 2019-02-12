<?php

namespace WebHappens\Prismic;

use Prismic\LinkResolver;

class DocumentUrlResolver extends LinkResolver
{
    public function resolve($data): ?string
    {
        $document = Document::newHydratedInstance($data);

        if ($document && $document->isLinkable()) {
            return $document->url;
        }

        return url('/');
    }
}
