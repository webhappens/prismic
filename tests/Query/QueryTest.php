<?php

namespace WebHappens\Prismic\Tests\Query;

use Prismic\Api;
use Prismic\SimplePredicate;
use WebHappens\Prismic\Query;
use WebHappens\Prismic\Tests\TestCase;

class QueryTest extends TestCase
{
    public function test_make()
    {
        $this->assertInstanceOf(Query::class, Query::make());
    }

    public function test_to_predicates()
    {
        $predicates = Query::make()
            ->where('id', '1')
            ->whereIn('example.name', ['ben', 'sam'])
            ->toPredicates();

        $this->assertIsArray($predicates);
        $this->assertCount(2, $predicates);
        $this->assertContainsOnlyInstancesOf(SimplePredicate::class, $predicates);
    }

    public function test_type_can_chain()
    {
        $this->assertInstanceOf(Query::class, Query::make()->type('document_a'));
    }

    public function test_options_can_chain()
    {
        $this->assertInstanceOf(Query::class, Query::make()->options([]));
    }

    public function test_options()
    {
        $options = Query::make()
            ->options(['foo' => 'a', 'bar' => 'b'])
            ->options(['baz' => 'c'])
            ->getOptions();

        $this->assertEquals(['foo' => 'a', 'bar' => 'b', 'baz' => 'c'], $options);
    }

    public function test_lang_option()
    {
        $options = Query::make()
            ->lang('*')
            ->options(['foo' => 'bar'])
            ->getOptions();

        $this->assertEquals(['lang' => '*', 'foo' => 'bar'], $options);

        $options = Query::make()
            ->lang('en_GB')
            ->options(['foo' => 'bar'])
            ->getOptions();

        $this->assertEquals(['lang' => 'en-gb', 'foo' => 'bar'], $options);
    }

    public function test_api_resolves_from_container()
    {
        $this->swap(Api::class, 'foobar');
        $this->assertEquals('foobar', Query::make()->api());
    }
}
