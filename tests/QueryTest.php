<?php

namespace WebHappens\Prismic\Tests;

use stdClass;
use Prismic\Api;
use Mockery as m;
use Prismic\SimplePredicate;
use WebHappens\Prismic\Query;
use WebHappens\Prismic\Document;
use Illuminate\Support\Collection;
use WebHappens\Prismic\Tests\Stubs\DocumentStub;

class QueryTest extends TestCase
{
    public function testFind()
    {
        $this->assertNull(DocumentStub::find(null));

        $document = DocumentStub::make();
        $queryMock = m::mock(Query::class . '[first,where]');
        $queryMock->setDocument($document);
        $queryMock->shouldReceive('where')->once()->with('id', 'foo')->andReturn($queryMock);
        $queryMock->shouldReceive('first')->once()->andReturn($document);
        $result = $queryMock->find('foo');
        $this->assertInstanceOf(Document::class, $result);
    }

    public function testFindMany()
    {
        $this->assertInstanceOf(Collection::class, DocumentStub::findMany([]));

        $document = DocumentStub::make();
        $queryMock = m::mock(Query::class . '[get,where]');
        $queryMock->setDocument($document);
        $queryMock->shouldReceive('where')->once()->with('id', 'in', ['foo1', 'foo2'])->andReturn($queryMock);
        $queryMock->shouldReceive('get')->once()->andReturn(collect());
        $results = $queryMock->findMany(['foo1', 'foo2']);
        $this->assertInstanceOf(Collection::class, $results);
    }

    public function testSingle()
    {
        $document = DocumentStub::make();
        $queryMock = m::mock(Query::class . '[first]');
        $queryMock->setDocument($document);
        $queryMock->shouldReceive('first')->once()->andReturn($document);
        $result = $queryMock->single();
        $this->assertInstanceOf(Document::class, $result);
    }

    public function testFirst()
    {
        $document = DocumentStub::make();
        $queryMock = m::mock(Query::class . '[get]');
        $queryMock->setDocument($document);
        $collectionMock = m::mock(Collection::class . '[first]');
        $queryMock->shouldReceive('get')->once()->andReturn($collectionMock);
        $collectionMock->shouldReceive('first');
        $result = $queryMock->first();
    }

    public function testWhereConvertsToPredicates()
    {
        $document = DocumentStub::make();

        $predicates = (new Query)
            ->setDocument($document)
            ->where('id', '1')
            ->where('name', 'in', ['ben', 'sam'])
            ->toPredicates();

        $this->assertCount(2, $predicates);
        $this->assertInstanceOf(SimplePredicate::class, $predicates[0]);
        $this->assertEquals('[:d = at(document.id, "1")]', $predicates[0]->q());
        $this->assertInstanceOf(SimplePredicate::class, $predicates[1]);
        $this->assertEquals('[:d = in(my.example_document.name, ["ben", "sam"])]', $predicates[1]->q());
    }

    public function testChunk()
    {
        $document = DocumentStub::make();

        $result = (object) array_fill_keys($document->getGlobalFieldKeys(), 'foo');
        $result->data = (object) ['field' => 'bar'];

        $raw = (object) [
            'total_pages' => 3,
            'results' => [$result, $result],
        ];

        $queryMock = m::mock(Query::class . '[options,getRaw]');
        $queryMock->setDocument($document);
        $queryMock->shouldReceive('options')->times(3)->andReturn($queryMock);
        $queryMock->shouldReceive('getRaw')->times(3)->andReturn($raw, $raw, $raw);

        $count = 0;

        $queryMock->chunk(2, function ($chunk) use (&$count) {
            foreach ($chunk as $document) {
                $count++;
                $this->assertInstanceOf(Document::class, $document);
            }
        });

        $this->assertEquals(6, $count);
    }

    public function testGetRaw()
    {
        $queryMock = m::mock(Query::class . '[api]');
        $apiMock = m::mock(Api::class);
        $queryMock->shouldReceive('api')->once()->andReturn($apiMock);
        $apiMock->shouldReceive('query')->once();
        $queryMock->getRaw();
    }

    public function testOptions()
    {
        $this->assertInstanceOf(Query::class, (new Query)->options([]));
    }

    public function testApi()
    {
        $this->swap(Api::class, 'foobar');
        $this->assertEquals('foobar', (new Query)->api());
    }
}
