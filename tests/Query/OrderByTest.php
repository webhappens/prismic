<?php

namespace WebHappens\Prismic\Tests\Query;

use InvalidArgumentException;
use WebHappens\Prismic\Query;
use WebHappens\Prismic\Tests\TestCase;

class OrderByTest extends TestCase
{
    public function test_order_by()
    {
        $query = Query::make()->type('example')->orderBy('id')->orderByDesc('foo');
        $orderings = '[document.id,my.example.foo desc]';

        $this->assertEquals(
            $query->getOptions(),
            ['orderings' => $orderings]
        );

        $this->assertEquals(
            $query->options(['pageSize' => 100])->getOptions(),
            [
                'orderings' => $orderings,
                'pageSize' => 100,
            ]
        );
    }

    public function test_order_by_invalid_direction()
    {
        $this->expectException(InvalidArgumentException::class);

        Query::make()->orderBy('id', 'asce');
    }
}
