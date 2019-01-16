<?php

namespace WebHappens\Prismic;

use Prismic\LinkResolver;
use WebHappens\Prismic\Contracts\Linkable;

class DocumentUrlResolver extends LinkResolver
{
    public function resolve($data): ?string
    {
        if ($document = Prismic::findDocumentByType(data_get($data, 'type'))) {
            $document = $document::make();

            if ($document instanceof Linkable) {
                return $document->getUrl();
            }
        }

        return url('/');
    }
}
