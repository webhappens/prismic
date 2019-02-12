<?php

namespace WebHappens\Prismic\Tests\Stubs;

use WebHappens\Prismic\Document;

class DocumentStub extends Document
{
    protected static $type = 'example';

    protected $maps = [
        'uri' => 'url',
    ];
}
