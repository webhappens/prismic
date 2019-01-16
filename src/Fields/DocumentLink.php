<?php

namespace WebHappens\Prismic\Fields;

use UnexpectedValueException;
use WebHappens\Prismic\Document;
use WebHappens\Prismic\Contracts\Linkable;

class DocumentLink extends Link
{
    protected $document;

    public function __construct(Document $document, $title = null)
    {
        if ( ! $document instanceOf Linkable) {
            throw new UnexpectedValueException('Document "' . get_class($document) . '" must implement Linkable interface.');
        }

        parent::__construct(
            $document->getUrl(),
            $title ?: $document->getTitle()
        );

        $this->document = $document;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }
}
