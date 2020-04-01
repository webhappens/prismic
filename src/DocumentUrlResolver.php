<?php

namespace WebHappens\Prismic;

use Prismic\LinkResolver;

class DocumentUrlResolver extends LinkResolver
{
    public function resolve($data): ?string
    {
        $document = Prismic::documentResolver($data);

        if ($document && $document->isLinkable()) {
            return $document->url;
        }

        return url('/');
    }
}
