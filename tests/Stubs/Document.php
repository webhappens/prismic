<?php

namespace WebHappens\Prismic\Tests\Stubs;

use WebHappens\Prismic\Document as BaseDocument;

class Document extends BaseDocument
{
    protected $type = 'my_document';

    protected $maps = [
        'uri' => 'my_url',
    ];
}
