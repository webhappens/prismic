<?php

namespace WebHappens\Prismic\Tests;

use Prismic\Api;
use Mockery as m;
use Hamcrest\Matchers;
use Prismic\SimplePredicate;
use InvalidArgumentException;
use WebHappens\Prismic\Query;
use Illuminate\Support\Collection;
use WebHappens\Prismic\Tests\Stubs\DocumentStub;

class QueryTest extends TestCase
{
    public function test_type_can_chain()
    {
        $query = (new Query)->type('example');
        $this->assertInstanceOf(Query::class, $query);
    }

    public function test_find()
    {
        $query = m::mock(Query::class . '[first,where]');
        $this->assertNull($query->find(null));
        $query->shouldReceive('where')->once()->with('id', 'foo')->andReturnSelf();
        $query->shouldReceive('first')->once()->andReturn(DocumentStub::make());
        $result = $query->find('foo');
        $this->assertInstanceOf(DocumentStub::class, $result);
    }

    public function test_find_many()
    {
        $query = m::mock(Query::class . '[get,where]');
        $this->assertInstanceOf(Collection::class, $query->findMany([]));
        $query->shouldReceive('where')->once()->with('id', 'in', ['foo1', 'foo2'])->andReturnSelf();
        $query->shouldReceive('get')->once()->andReturn(collect());
        $results = $query->findMany(['foo1', 'foo2']);
        $this->assertInstanceOf(Collection::class, $results);
    }

    public function test_single()
    {
        $query = m::mock(Query::class . '[first]');
        $query->shouldReceive('first')->once()->andReturn(DocumentStub::make());
        $result = $query->single();
        $this->assertInstanceOf(DocumentStub::class, $result);
    }

    public function test_where_can_chain()
    {
        $query = (new Query)->where('id', '1');
        $this->assertInstanceOf(Query::class, $query);
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
        $query = m::mock(Query::class . '[chunk]');
        $query->shouldReceive('chunk')->once();
        $this->assertInstanceOf(Collection::class, $query->get());
    }

    public function test_first()
    {
        $query = m::mock(Query::class . '[get]');
        $collection = m::mock(Collection::class . '[first]');
        $query->shouldReceive('get')->once()->andReturn($collection);
        $collection->shouldReceive('first');
        $query->first();
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

        $this->expectException(InvalidArgumentException::class);

        $query->chunk(101, function () {
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
}
