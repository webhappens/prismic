<?php

namespace WebHappens\Prismic\Tests;

use Prismic\Api;
use Mockery as m;
use Hamcrest\Matchers;
use Prismic\SimplePredicate;
use InvalidArgumentException;
use WebHappens\Prismic\Query;
use WebHappens\Prismic\Prismic;
use Illuminate\Support\Collection;
use WebHappens\Prismic\Tests\Stubs\DocumentAStub;
use WebHappens\Prismic\Tests\Stubs\DocumentBStub;

class QueryTest extends TestCase
{
    public function test_type_can_chain()
    {
        $this->assertInstanceOf(Query::class, (new Query)->type('document_a'));
    }

    public function test_find()
    {
        $this->assertNull((new Query())->find(null));

        $expectedPredicates = (new Query)->where('id', 1)->toPredicates();
        $query = $this->mockApiQuery($expectedPredicates, [], $this->mockRawStubSingle());

        Prismic::documents([DocumentAStub::class]);
        $result = $query->find(1);
        $this->assertInstanceOf(DocumentAStub::class, $result);
        Prismic::$documents = [];
    }

    public function test_find_many()
    {
        $results = (new Query())->findMany([]);
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEmpty($results);

        $expectedPredicates = (new Query)->where('id', 'in', [1, 2, 3])->toPredicates();
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

    public function test_single()
    {
        $this->assertNull((new Query)->single());

        $expectedPredicates = (new Query)->where('type', 'document_a')->toPredicates();
        $query = $this->mockApiQuery($expectedPredicates, [], $this->mockRawStubSingle());
        $query->type('document_a');

        Prismic::documents([DocumentAStub::class]);
        $result = $query->single();
        $this->assertInstanceOf(DocumentAStub::class, $result);
        Prismic::$documents = [];
    }

    public function test_where_can_chain()
    {
        $this->assertInstanceOf(Query::class, (new Query)->where('id', '1'));
    }

    public function test_where_with_global_field()
    {
        $predicates = (new Query)
            ->where('id', '1')
            ->toPredicates();

        $this->assertCount(1, $predicates);
        $this->assertEquals('[:d = at(document.id, "1")]', $predicates[0]->q());
    }

    public function test_where_without_type()
    {
        $predicates = (new Query)
            ->where('example.name', 'in', ['ben', 'sam'])
            ->toPredicates();

        $this->assertCount(1, $predicates);
        $this->assertEquals('[:d = in(my.example.name, ["ben", "sam"])]', $predicates[0]->q());
    }

    public function test_where_with_type()
    {
        $predicates = (new Query)
            ->type('example')
            ->where('name', 'in', ['ben', 'sam'])
            ->where('example.foo', 'bar')
            ->toPredicates();

        $this->assertCount(3, $predicates);
        $this->assertEquals('[:d = at(document.type, "example")]', $predicates[0]->q());
        $this->assertEquals('[:d = in(my.example.name, ["ben", "sam"])]', $predicates[1]->q());
        $this->assertEquals('[:d = at(my.example.foo, "bar")]', $predicates[2]->q());
    }

    public function test_get()
    {
        $expectedPredicates = (new Query)->toPredicates();
        $expectedOptions = ['pageSize' => 100, 'page' => 1];
        $query = $this->mockApiQuery($expectedPredicates, $expectedOptions, $this->mockRawStubMany());

        Prismic::documents([DocumentAStub::class, DocumentBStub::class]);
        $results = $query->get();
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(DocumentAStub::class, $results[0]);
        $this->assertInstanceOf(DocumentAStub::class, $results[1]);
        $this->assertInstanceOf(DocumentBStub::class, $results[2]);
        Prismic::$documents = [];
    }

    public function test_first()
    {
        $expectedPredicates = (new Query)->toPredicates();
        $query = $this->mockApiQuery($expectedPredicates, [], $this->mockRawStubMany());

        Prismic::documents([DocumentAStub::class]);
        $result = $query->first();
        $this->assertInstanceOf(DocumentAStub::class, $result);
        Prismic::$documents = [];
    }

    public function test_chunk()
    {
        $responseStub = (object) [
            'total_pages' => 3,
            'results' => [],
        ];

        $query = m::mock(Query::class . '[options,getRaw]');
        $query->shouldReceive('options')->times(3)->andReturnSelf();
        $query->shouldReceive('getRaw')->times(3)->andReturn($responseStub);

        $count = 0;
        $query->chunk(100, function ($chunk) use (&$count) {
            $count++;
            $this->assertInstanceOf(Collection::class, $chunk);
        });

        $this->assertEquals(3, $count);
    }

    public function test_chunk_exceeding_chunk_limit()
    {
        $this->expectException(InvalidArgumentException::class);

        (new Query)->chunk(101, function () {
            // do nothing
        });
    }

    public function test_to_predicates()
    {
        $predicates = (new Query)
            ->where('id', '1')
            ->where('example.name', 'in', ['ben', 'sam'])
            ->toPredicates();

        $this->assertInternalType('array', $predicates);
        $this->assertCount(2, $predicates);
        $this->assertContainsOnlyInstancesOf(SimplePredicate::class, $predicates);
    }

    public function test_get_raw()
    {
        $query = m::mock(Query::class . '[api]');
        $api = m::mock(Api::class);
        $query->options(['foo' => 'bar']);
        $query->where('id', '1')->where('name', 'in', ['ben', 'sam']);
        $query->shouldReceive('api')->once()->andReturn($api);
        $api->shouldReceive('query')->once()->with($query->toPredicates(), ['foo' => 'bar']);
        $query->getRaw();
    }

    public function test_options_can_chain()
    {
        $this->assertInstanceOf(Query::class, (new Query)->options([]));
    }

    public function test_api_returns_prismic_api_instance()
    {
        $this->swap(Api::class, 'foobar');
        $this->assertEquals('foobar', (new Query)->api());
    }

    protected function mockApiQuery($expectedPredicates, $expectedOptions, $return)
    {
        $api = m::mock(Api::class);
        $api->shouldReceive('query')->with($expectedPredicates, $expectedOptions)->andReturn($return);

        $query = m::mock(Query::class . '[api]');
        $query->shouldReceive('api')->once()->andReturn($api);

        return $query;
    }

    protected function mockRawStubSingle()
    {
        return (object) [
            'total_pages' => 1,
            'results' => [
                (object) ['id' => '1', 'type' => 'document_a', 'data' => []],
            ],
        ];
    }

    protected function mockRawStubMany()
    {
        return (object) [
            'total_pages' => 1,
            'results' => [
                (object) ['id' => '1', 'type' => 'document_a', 'data' => []],
                (object) ['id' => '2', 'type' => 'document_a', 'data' => []],
                (object) ['id' => '3', 'type' => 'document_b', 'data' => []],
            ],
        ];
    }
}
