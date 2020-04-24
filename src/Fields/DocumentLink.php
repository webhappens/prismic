<?php

namespace WebHappens\Prismic\Fields;

use UnexpectedValueException;
use WebHappens\Prismic\Document;

class DocumentLink extends Link
{
    protected $document;

    public function __construct(Document $document, $title = null)
    {
        if (! $document->isLinkable()) {
            throw new UnexpectedValueException('Document "'.get_class($document).'" must have "url" and "title" attributes set.');
        }

        parent::__construct(
            $document->url,
            $title ?: $document->title
        );

        $this->document = $document;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }
}
