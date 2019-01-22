<?php

namespace WebHappens\Prismic;

class DocumentResolver
{
    public function resolve($data): ?Document
    {
        if ($document = Document::resolveClassFromType(data_get($data, 'type'))) {
            return $document::isSingle() ? $document::single() : $document::find(data_get($data, 'id'));
        }

        return null;
    }
}
