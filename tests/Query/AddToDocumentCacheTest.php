<?php

namespace WebHappens\Prismic\Tests\Query;

use Mockery;
use Prismic\Api;
use WebHappens\Prismic\Query;
use WebHappens\Prismic\Prismic;
use WebHappens\Prismic\Tests\TestCase;
use WebHappens\Prismic\Tests\Stubs\DocumentAStub;
use WebHappens\Prismic\Tests\Stubs\DocumentBStub;

class AddToDocumentCacheTest extends TestCase
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

    public function test_find_adds_to_cache()
    {
        $this->swap(Api::class, Mockery::mock(Api::class, function($mock) {
            $mock->shouldReceive('query')
                ->andReturn(
                    (object) [
                        'page' => 1,
                        'total_pages' => 1,
                        'total_results_size' => 1,
                        'results' => [
                            (object) ['id' => '1', 'type' => 'document_a'],
                        ],
                    ]
                );
        }));

        $model = Query::make()->cache()->find(1);

        $cache = Query::documentCache();
        $this->assertCount(1, $cache);
        $this->assertEquals($model, $cache->get(1));
    }

    public function test_find_many_adds_to_cache()
    {
        $this->swap(Api::class, Mockery::mock(Api::class, function($mock) {
            $mock->shouldReceive('query')
                ->andReturn(
                    (object) [
                        'page' => 1,
                        'total_pages' => 1,
                        'total_results_size' => 3,
                        'results' => [
                            (object) ['id' => '1', 'type' => 'document_a'],
                            (object) ['id' => '2', 'type' => 'document_a'],
                            (object) ['id' => '3', 'type' => 'document_b'],
                        ],
                    ]
                );
        }));

        $models = Query::make()->cache()->findMany([1, 2, 3]);

        $cache = Query::documentCache();
        $this->assertCount(3, $cache);
        $models->each(function($model) use ($cache) {
            $this->assertEquals($model, $cache->get($model->id));
        });
    }

    public function test_single_adds_to_cache()
    {
        $this->swap(Api::class, $this->partialMock(Api::class, function($mock) {
            $mock->shouldReceive('getSingle')
                ->once()
                ->with('document_a')
                ->andReturn((object) ['id' => '1', 'type' => 'document_a']);
        }));

        $model = Query::make()->cache()->single('document_a');

        $cache = Query::documentCache();
        $this->assertCount(1, $cache);
        $this->assertEquals($model, $cache->get(1));
    }
}
