<?php

namespace WebHappens\Prismic\Events;

class DocumentsUpdated
{
    public $masterRef;
    public $documents = [];

    public function __construct($masterRef, array $documents)
    {
        $this->masterRef = $masterRef;
        $this->documents = $documents;
    }
}
