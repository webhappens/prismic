<?php

namespace WebHappens\Prismic\Tests;

use WebHappens\Prismic\Prismic;

class DocumentTest extends TestCase
{
    public function testRegisteredDocuments()
    {
        $documents = [
            'App\Article',
            'App\Collection',
        ];

        Prismic::documents($documents);

        $this->assertArraySubset($documents, Prismic::$documents);
    }
}
