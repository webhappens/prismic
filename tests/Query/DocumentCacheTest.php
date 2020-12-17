<?php

namespace WebHappens\Prismic\Tests\Query;

use Mockery;
use Prismic\Api;
use WebHappens\Prismic\Query;
use WebHappens\Prismic\Prismic;
use WebHappens\Prismic\Tests\TestCase;
use WebHappens\Prismic\Tests\Stubs\DocumentAStub;
use WebHappens\Prismic\Tests\Stubs\DocumentBStub;

class DocumentCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Prismic::documents([DocumentAStub::class, DocumentBStub::class]);

        $this->assertEmpty(Query::documentCache());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Query::clearDocumentCache();
    }

    public function test_interaction_with_document_cache()
    {
        $stubs = collect([
            Prismic::documentResolver('document_a', ['id' => '1', 'foo' => 'bar']),
            Prismic::documentResolver('document_a', ['id' => '2', 'foo' => 'bar']),
            Prismic::documentResolver('document_b', ['id' => '3', 'foo' => 'bar']),
        ]);

        $set = Query::setDocumentCache($stubs);
        $this->assertEquals($stubs->keyBy('id'), $set);
        $this->assertEquals($stubs->keyBy('id'), Query::documentCache());

        $additionalStubs = collect([
            Prismic::documentResolver('document_a', ['id' => '4', 'foo' => 'bar']),
            Prismic::documentResolver('document_b', ['id' => '5', 'foo' => 'bar']),
        ]);

        $addTo = Query::addToDocumentCache($additionalStubs);
        $this->assertEquals($additionalStubs->keyBy('id'), $addTo);
        $this->assertEquals($stubs->merge($additionalStubs)->keyBy('id'), Query::documentCache());

        $clear = Query::clearDocumentCache();
        $this->assertEquals(collect(), $clear);
        $this->assertEquals(collect(), Query::documentCache());
    }

    public function test_cache_can_chain()
    {
        $this->assertInstanceOf(Query::class, Query::make()->cache());
    }

    public function test_dont_cache_can_chain()
    {
        $this->assertInstanceOf(Query::class, Query::make()->dontCache());
    }
}
