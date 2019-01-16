<?php

namespace WebHappens\Prismic;

use WebHappens\Prismic\Prismic;
use WebHappens\Prismic\Document;

class DocumentResolver
{
    public function resolve($data): ?Document
    {
        if ($document = Prismic::findDocumentByType(data_get($data, 'type'))) {
            return $document::isSingle() ? $document::single() : $document::find(data_get($data, 'id'));
        }

        return null;
    }
}
