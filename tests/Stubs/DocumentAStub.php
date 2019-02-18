<?php

namespace WebHappens\Prismic\Tests\Stubs;

use WebHappens\Prismic\Document;

class DocumentAStub extends Document
{
    protected static $type = 'document_a';

    protected $maps = [
        'uri' => 'url',
    ];

    protected $casts = [
        'url' => 'url',
    ];
}
