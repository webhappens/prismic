<?php

namespace WebHappens\Prismic\Tests;

use Illuminate\Support\Collection;
use Mockery as m;
use WebHappens\Prismic\Query;
use WebHappens\Prismic\Relation;

class RelationTest extends TestCase
{
    public function test_one()
    {
        $query = m::mock(Query::class);
        $query->shouldReceive('find')->once()->with(1);

        $relation = m::mock(Relation::class)->makePartial();
        $relation->shouldAllowMockingProtectedMethods()->shouldReceive('query')->once()->andReturn($query);

        $relation->one((object) ['id' => 1, 'isBroken' => false]);
    }

    public function test_one_returns_null_if_no_id()
    {
        $this->assertNull(
            (new Relation)->one((object) ['foo' => 'bar'])
        );
    }

    public function test_one_returns_null_if_is_broken()
    {
        $this->assertNull(
            (new Relation)->one((object) ['id' => 1, 'isBroken' => true])
        );
    }

    public function test_many()
    {
        $query = m::mock(Query::class);
        $query->shouldReceive('findMany')->once()->with([1, 3]);

        $relation = m::mock(Relation::class)->makePartial();
        $relation->shouldAllowMockingProtectedMethods()->shouldReceive('query')->once()->andReturn($query);

        $relation->many([
            (object) [
                'article' => (object) ['id' => 1, 'isBroken' => false],
            ],
            (object) [
                'article' => (object) ['id' => 2, 'isBroken' => true],
            ],
            (object) [
                'article' => (object) ['id' => 3, 'isBroken' => false],
            ],
            (object) [
                'article' => (object) ['foo' => 'bar'],
            ],
        ], 'article');
    }

    public function test_many_returns_empty_collection_when_data_empty_or_null()
    {
        $relation = (new Relation);

        $this->assertInstanceOf(Collection::class, $relation->many([], 'article'));
        $this->assertEmpty($relation->many([], 'article'));
        $this->assertInstanceOf(Collection::class, $relation->many(collect(), 'article'));
        $this->assertEmpty($relation->many(collect(), 'article'));
        $this->assertInstanceOf(Collection::class, $relation->many(null, 'article'));
        $this->assertEmpty($relation->many(null, 'article'));
    }
}
