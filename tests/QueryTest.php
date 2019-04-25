<?php

namespace WebHappens\Prismic\Tests;

use Prismic\Api;
use Mockery as m;
use Illuminate\Support\Arr;
use Prismic\SimplePredicate;
use InvalidArgumentException;
use WebHappens\Prismic\Query;
use WebHappens\Prismic\Prismic;
use Illuminate\Support\Collection;
use WebHappens\Prismic\Tests\Stubs\DocumentAStub;
use WebHappens\Prismic\Tests\Stubs\DocumentBStub;

class QueryTest extends TestCase
{
    public function test_make()
    {
        $this->assertInstanceOf(Query::class, Query::make());
    }

    public function test_eager_load_all()
    {
        $api = m::mock(Api::class);
        $api->shouldReceive('query')
            ->once()
            ->with([], ['pageSize' => 100, 'page' => 1])
            ->andReturn($this->mockRawStubMany());
        $this->swap(Api::class, $api);

        $this->assertManyAddedToCache(function () {
            $query = Query::eagerLoadAll();
            $this->assertInstanceOf(Query::class, $query);
        });
    }

    public function test_interaction_with_document_cache()
    {
        $this->assertEmpty(Query::documentCache());

        $stubs = collect([
            ['id' => '1', 'foo' => 'bar'],
            ['id' => '2', 'foo' => 'bar'],
            ['id' => '3', 'foo' => 'bar'],
        ]);

        $set = Query::setDocumentCache($stubs);
        $this->assertEquals($stubs->keyBy('id'), $set);
        $this->assertEquals($stubs->keyBy('id'), Query::documentCache());

        $additionalStubs = collect([
            ['id' => '4', 'foo' => 'bar'],
            ['id' => '5', 'foo' => 'bar'],
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

    public function test_type_can_chain()
    {
        $this->assertInstanceOf(Query::class, Query::make()->type('document_a'));
    }

    public function test_find()
    {
        $this->assertNull(Query::make()->find(null));

        $expectedPredicates = Query::make()->where('id', 1)->toPredicates();
        $query = $this->mockApiQuery($expectedPredicates, [], $this->mockRawStubSingle());

        Prismic::documents([DocumentAStub::class]);
        $result = $query->find(1);
        $this->assertInstanceOf(DocumentAStub::class, $result);
        Prismic::$documents = [];
    }

    public function test_find_adds_to_cache()
    {
        $this->assertSingleAddedToCache(function () {
            $expectedPredicates = Query::make()->where('id', 1)->toPredicates();
            $query = $this->mockApiQuery($expectedPredicates, [], $this->mockRawStubSingle());
            $query->cache()->find(1);
        });
    }

    public function test_find_uses_cache()
    {
        $this->assertUsesCache(function ($query) {
            return $query->find(2);
        });
    }

    public function test_find_many()
    {
        $results = Query::make()->findMany([]);
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEmpty($results);

        $expectedPredicates = Query::make()->whereIn('id', [1, 2, 3])->toPredicates();
        $expectedOptions = ['pageSize' => 100, 'page' => 1];
        $query = $this->mockApiQuery($expectedPredicates, $expectedOptions, $this->mockRawStubMany());

        Prismic::documents([DocumentAStub::class, DocumentBStub::class]);
        $results = $query->findMany([1, 2, 3]);
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(DocumentAStub::class, $results[0]);
        $this->assertInstanceOf(DocumentAStub::class, $results[1]);
        $this->assertInstanceOf(DocumentBStub::class, $results[2]);
        Prismic::$documents = [];
    }

    public function test_find_many_adds_to_cache()
    {
        $this->assertManyAddedToCache(function () {
            $expectedPredicates = Query::make()->whereIn('id', [1, 2, 3])->toPredicates();
            $expectedOptions = ['pageSize' => 100, 'page' => 1];
            $query = $this->mockApiQuery($expectedPredicates, $expectedOptions, $this->mockRawStubMany());
            $query->cache()->findMany([1, 2, 3]);
        });
    }

    public function test_find_many_uses_cache()
    {
        $this->assertUsesCache(function ($query) {
            return $query->findMany([2, 3])->toArray();
        });
    }

    public function test_single()
    {
        $this->assertNull(Query::make()->single());

        $expectedPredicates = Query::make()->where('type', 'document_a')->toPredicates();
        $query = $this->mockApiQuery($expectedPredicates, [], $this->mockRawStubSingle());
        $query->type('document_a');

        Prismic::documents([DocumentAStub::class]);
        $result = $query->single();
        $this->assertInstanceOf(DocumentAStub::class, $result);
        Prismic::$documents = [];
    }

    public function test_single_adds_to_cache()
    {
        $this->assertSingleAddedToCache(function () {
            $expectedPredicates = Query::make()->where('type', 'document_a')->toPredicates();
            $query = $this->mockApiQuery($expectedPredicates, [], $this->mockRawStubSingle());
            $query->type('document_a');
            $query->cache()->single(1);
        });
    }

    public function test_single_uses_cache()
    {
        $this->assertUsesCache(function ($query) {
            return $query->single();
        });
    }

    public function test_where_can_chain()
    {
        $this->assertInstanceOf(Query::class, Query::make()->where('id', '1'));
    }

    public function test_where_with_global_field()
    {
        $predicates = Query::make()
            ->where('id', '1')
            ->toPredicates();

        $this->assertCount(1, $predicates);
        $this->assertEquals('[:d = at(document.id, "1")]', $predicates[0]->q());
    }

    public function test_where_without_type()
    {
        $predicates = Query::make()
            ->whereIn('example.name', ['ben', 'sam'])
            ->toPredicates();

        $this->assertCount(1, $predicates);
        $this->assertEquals('[:d = in(my.example.name, ["ben", "sam"])]', $predicates[0]->q());
    }

    public function test_where_with_type()
    {
        $predicates = Query::make()
            ->type('example')
            ->whereIn('name', ['ben', 'sam'])
            ->where('example.foo', 'bar')
            ->toPredicates();

        $this->assertCount(3, $predicates);
        $this->assertEquals('[:d = at(document.type, "example")]', $predicates[0]->q());
        $this->assertEquals('[:d = in(my.example.name, ["ben", "sam"])]', $predicates[1]->q());
        $this->assertEquals('[:d = at(my.example.foo, "bar")]', $predicates[2]->q());
    }

    public function test_get()
    {
        $expectedOptions = ['pageSize' => 100, 'page' => 1];
        $query = $this->mockApiQuery([], $expectedOptions, $this->mockRawStubMany());

        Prismic::documents([DocumentAStub::class, DocumentBStub::class]);
        $results = $query->get();
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(DocumentAStub::class, $results[0]);
        $this->assertInstanceOf(DocumentAStub::class, $results[1]);
        $this->assertInstanceOf(DocumentBStub::class, $results[2]);
        Prismic::$documents = [];
    }

    public function test_get_adds_to_cache()
    {
        $this->assertManyAddedToCache(function () {
            $query = $this->mockApiQuery([], ['pageSize' => 100, 'page' => 1], $this->mockRawStubMany());
            $query->cache()->get();
        });
    }

    public function test_first()
    {
        $query = $this->mockApiQuery([], [], $this->mockRawStubMany());

        Prismic::documents([DocumentAStub::class]);
        $result = $query->first();
        $this->assertInstanceOf(DocumentAStub::class, $result);
        Prismic::$documents = [];
    }

    public function test_first_adds_to_cache()
    {
        $this->assertSingleAddedToCache(function () {
            $query = $this->mockApiQuery([], [], $this->mockRawStubMany());
            $query->cache()->first(1);
        });
    }

    public function test_chunk()
    {
        $rawStubs = [
            (object) [
                'total_pages' => 3,
                'results' => [
                    (object) ['id' => '1', 'type' => 'document_a'],
                    (object) ['id' => '2', 'type' => 'document_a'],
                ],
            ],
            (object) [
                'total_pages' => 3,
                'results' => [
                    (object) ['id' => '3', 'type' => 'document_a'],
                    (object) ['id' => '4', 'type' => 'document_b'],
                ],
            ],
            (object) [
                'total_pages' => 3,
                'results' => [
                    (object) ['id' => '5', 'type' => 'document_b'],
                ],
            ],
        ];

        $expectedOptions = m::anyOf(
            ['pageSize' => 2, 'page' => 1],
            ['pageSize' => 2, 'page' => 2],
            ['pageSize' => 2, 'page' => 3]
        );
        $query = $this->mockApiQuery([], $expectedOptions, $rawStubs);

        Prismic::documents([DocumentAStub::class, DocumentBStub::class]);
        $documents = collect();
        $query->chunk(2, function ($chunk) use ($documents) {
            $documents->push($chunk);
        });
        $documents = $documents->flatten();
        $this->assertCount(5, $documents);
        $this->assertInstanceOf(DocumentAStub::class, $documents[0]);
        $this->assertInstanceOf(DocumentAStub::class, $documents[1]);
        $this->assertInstanceOf(DocumentAStub::class, $documents[2]);
        $this->assertInstanceOf(DocumentBStub::class, $documents[3]);
        $this->assertInstanceOf(DocumentBStub::class, $documents[4]);
        Prismic::$documents = [];
    }

    public function test_chunk_exceeding_chunk_limit()
    {
        $this->expectException(InvalidArgumentException::class);

        Query::make()->chunk(101, function () {
            // do nothing
        });
    }

    public function test_chunk_adds_to_cache()
    {
        $this->assertManyAddedToCache(function () {
            $query = $this->mockApiQuery([], ['pageSize' => 100, 'page' => 1], $this->mockRawStubMany());
            $query->cache()->chunk(100, function () {
                // do nothing
            });
        });
    }

    public function test_to_predicates()
    {
        $predicates = Query::make()
            ->where('id', '1')
            ->whereIn('example.name', ['ben', 'sam'])
            ->toPredicates();

        $this->assertInternalType('array', $predicates);
        $this->assertCount(2, $predicates);
        $this->assertContainsOnlyInstancesOf(SimplePredicate::class, $predicates);
    }

    public function test_get_raw()
    {
        $query = $this->mockApiQuery([], [], $this->mockRawStubSingle());
        $this->assertEquals($this->mockRawStubSingle(), $query->getRaw());
    }

    public function test_options_can_chain()
    {
        $this->assertInstanceOf(Query::class, Query::make()->options([]));
    }

    public function test_api_returns_prismic_api_instance()
    {
        $this->swap(Api::class, 'foobar');
        $this->assertEquals('foobar', Query::make()->api());
    }

    protected function assertUsesCache(callable $callback)
    {
        Prismic::documents([DocumentAStub::class, DocumentBStub::class]);
        $query = $this->mockApiQuery([], ['pageSize' => 100, 'page' => 1], $this->mockRawStubMany());
        $query->cache()->get();

        $query = m::mock(Query::class . '[api]');
        $query->shouldReceive('api')->never();
        $results = Arr::wrap($callback($query));

        foreach ($results as $result) {
            $this->assertEquals($result, Query::documentCache()->get($result->id));
        }

        Query::clearDocumentCache();
        Prismic::$documents = [];
    }

    protected function assertSingleAddedToCache(callable $callback)
    {
        $this->assertEmpty(Query::documentCache());

        Prismic::documents([DocumentAStub::class]);
        $callback();
        $cache = Query::documentCache();
        $this->assertCount(1, $cache);
        $this->assertInstanceOf(DocumentAStub::class, $cache[1]);
        Query::clearDocumentCache();
        Prismic::$documents = [];
    }

    protected function assertManyAddedToCache(callable $callback)
    {
        $this->assertEmpty(Query::documentCache());

        Prismic::documents([DocumentAStub::class, DocumentBStub::class]);
        $callback();
        $cache = Query::documentCache();
        $this->assertCount(3, $cache);
        $this->assertInstanceOf(DocumentAStub::class, $cache[1]);
        $this->assertInstanceOf(DocumentAStub::class, $cache[2]);
        $this->assertInstanceOf(DocumentBStub::class, $cache[3]);
        Query::clearDocumentCache();
        Prismic::$documents = [];
    }

    protected function mockApiQuery($expectedPredicates, $expectedOptions, $return)
    {
        $return = Arr::wrap($return);
        $times = count($return);

        $api = m::mock(Api::class);
        $api->shouldReceive('query')
            ->with($expectedPredicates, $expectedOptions)
            ->times($times)
            ->andReturn(...$return);

        $query = m::mock(Query::class . '[api]');
        $query->shouldReceive('api')->times($times)->andReturn($api);

        return $query;
    }

    protected function mockRawStubSingle()
    {
        return (object) [
            'total_pages' => 1,
            'results' => [
                (object) ['id' => '1', 'type' => 'document_a'],
            ],
        ];
    }

    protected function mockRawStubMany()
    {
        return (object) [
            'total_pages' => 1,
            'results' => [
                (object) ['id' => '1', 'type' => 'document_a'],
                (object) ['id' => '2', 'type' => 'document_a'],
                (object) ['id' => '3', 'type' => 'document_b'],
            ],
        ];
    }
}
