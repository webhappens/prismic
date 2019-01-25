<?php

namespace WebHappens\Prismic\Tests;

use stdClass;
use Prismic\Api;
use Mockery as m;
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
