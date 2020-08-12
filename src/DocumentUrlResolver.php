<?php

namespace WebHappens\Prismic;

use Prismic\LinkResolver;

class DocumentUrlResolver extends LinkResolver
{
    public function resolve($data): ?string
    {
        $document = Prismic::documentResolver($data);

        if ($document && isset($document->url)) {
            return $document->url;
        }

        if ($document && method_exists($document, 'url')) {
            return $document->url();
        }

        return url('/');
    }
}
