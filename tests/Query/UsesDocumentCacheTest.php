<?php

namespace WebHappens\Prismic\Tests\Query;

use Mockery;
use Prismic\Api;
use WebHappens\Prismic\Query;
use WebHappens\Prismic\Prismic;
use WebHappens\Prismic\Tests\TestCase;
use WebHappens\Prismic\Tests\Stubs\DocumentAStub;
use WebHappens\Prismic\Tests\Stubs\DocumentBStub;

class UsesDocumentCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Prismic::documents([DocumentAStub::class, DocumentBStub::class]);

        $this->swap(Api::class, Mockery::mock(Api::class, function($mock) {
            $mock->shouldReceive('query')
                ->andReturn(
                    (object) [
                        'page' => 1,
                        'total_pages' => 1,
                        'total_results_size' => 5,
                        'results' => [
                            (object) ['id' => '1', 'type' => 'document_a'],
                            (object) ['id' => '2', 'type' => 'document_a'],
                            (object) ['id' => '3', 'type' => 'document_a'],
                            (object) ['id' => '4', 'type' => 'document_b'],
                            (object) ['id' => '5', 'type' => 'document_b'],
                        ],
                    ]
                );
        }));
    }

    public function test_eager_load_all()
    {
        $this->assertEmpty(Query::documentCache());

        $query = Query::eagerLoadAll();
        $this->assertInstanceOf(Query::class, $query);

        $cache = Query::documentCache();
        $this->assertCount(5, $cache);
        $this->assertInstanceOf(DocumentAStub::class, $cache[1]);
        $this->assertInstanceOf(DocumentAStub::class, $cache[2]);
        $this->assertInstanceOf(DocumentAStub::class, $cache[3]);
        $this->assertInstanceOf(DocumentBStub::class, $cache[4]);
        $this->assertInstanceOf(DocumentBStub::class, $cache[5]);
    }

    /**
     * @depends test_eager_load_all
     */
    public function test_find_uses_cache()
    {
        $this->partialMock(Query::class, function($mock) {
            $mock->shouldReceive('api')->never();
        });

        $model = Query::make()->cache()->find(2);
        $this->assertEquals($model, Query::documentCache()->get(2));
    }

    /**
     * @depends test_eager_load_all
     */
    public function test_find_many_uses_cache()
    {
        $this->partialMock(Query::class, function($mock) {
            $mock->shouldReceive('api')->never();
        });

        Query::make()->cache()->findMany([1, 2, 3])
            ->each(function($model) {
                $this->assertEquals($model, Query::documentCache()->get($model->id));
            });
    }
}
