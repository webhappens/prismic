<?php

namespace WebHappens\Prismic\Tests;

use stdClass;
use Mockery as m;
use WebHappens\Prismic\Query;
use WebHappens\Prismic\Slice;
use WebHappens\Prismic\Prismic;
use WebHappens\Prismic\Document;
use Illuminate\Support\Collection;
use WebHappens\Prismic\DocumentResolver;
use WebHappens\Prismic\Tests\Stubs\SliceAStub;
use WebHappens\Prismic\Tests\Stubs\SliceBStub;
use WebHappens\Prismic\Tests\Stubs\DocumentStub;

class DocumentTest extends TestCase
{
    public function test_resolve()
    {
        $item = new stdClass;
        $documentResolver = m::mock(DocumentResolver::class);
        $documentResolver->shouldReceive('resolve')->once()->with($item)->andReturn(new DocumentStub);
        $this->swap(DocumentResolver::class, $documentResolver);
        $this->assertInstanceOf(DocumentStub::class, Document::resolve($item));
    }

    public function test_resolve_many()
    {
        $items = ['a', 'b', 'c'];
        $documentResolver = m::mock(DocumentResolver::class);
        $documentResolver->shouldReceive('resolveMany')->once()->with($items)->andReturn(collect('d', 'e', 'f'));
        $this->swap(DocumentResolver::class, $documentResolver);
        $this->assertInstanceOf(Collection::class, Document::resolveMany($items));
    }

    public function test_make()
    {
        $this->assertInstanceOf(Document::class, DocumentStub::make());
    }

    public function test_get_type()
    {
        $this->assertEquals('example', DocumentStub::getType());
    }

    public function test_get_global_field_keys()
    {
        $this->assertEquals(DocumentStub::getGlobalFieldKeys(), [
            'id', 'uid', 'type', 'href', 'tags', 'first_publication_date',
            'last_publication_date', 'lang', 'alternate_languages',
        ]);
    }

    public function test_resolve_class_from_type()
    {
        Prismic::documents([DocumentStub::class]);
        $this->assertEquals(DocumentStub::class, Document::resolveClassFromType('example'));
        Prismic::$documents = [];
    }

    public function test_new_hydrated_instance()
    {
        $this->assertNull(Document::newHydratedInstance(new stdClass));

        $resultStub = (object) [
            'type' => 'example',
            'id' => '1',
            'data' => [
                'foo' => 'bar',
                'uri' => 'my-article', // Maps to `url` then casts to url
            ],
        ];

        Prismic::documents([DocumentStub::class]);
        $document = Document::newHydratedInstance($resultStub);
        $this->assertInstanceOf(DocumentStub::class, $document);
        $this->assertEquals('1', $document->id);
        $this->assertEquals('bar', $document->foo);
        $this->assertEquals(url('my-article'), $document->url);
        Prismic::$documents = [];
    }

    public function test_is_single()
    {
        $this->assertFalse(DocumentStub::isSingle());
    }

    public function test_is_linkable()
    {
        $document = new DocumentStub;
        $this->assertFalse($document->isLinkable());
        $document->title = 'foo';
        $document->url = 'https://example.org';
        $this->assertTrue($document->isLinkable());
    }

    public function test_get_slices()
    {
        Prismic::slices([SliceAStub::class, SliceBStub::class]);
        $document = new DocumentStub;
        $document->body = [
            ['slice_type' => 'slice_a'],
            ['slice_type' => 'slice_b'],
        ];

        $allSlices = $document->getSlices();
        $this->assertInstanceOf(Collection::class, $allSlices);
        $this->assertCount(2, $allSlices);
        $this->assertContainsOnlyInstancesOf(Slice::class, $allSlices);

        $sliceA = $document->getSlices('slice_a');
        $this->assertInstanceOf(Collection::class, $sliceA);
        $this->assertCount(1, $sliceA);
        $this->assertContainsOnlyInstancesOf(SliceAStub::class, $sliceA);

        Prismic::$slices = [];
    }

    public function test_new_query()
    {
        $query = (new DocumentStub)->newQuery();
        $predicates = $query->toPredicates();
        $this->assertInstanceOf(Query::class, $query);
        $this->assertCount(1, $predicates);
        $this->assertEquals('[:d = at(document.type, "example")]', $predicates[0]->q());
    }

    public function test_get_maps()
    {
        $this->assertEquals([
            'href' => 'api_id',
            'first_publication_date' => 'first_published',
            'last_publication_date' => 'last_published',
            'lang' => 'language',
            'uri' => 'url',
        ], (new DocumentStub)->getMaps());
    }

    public function test_get_casts()
    {
        $this->assertEquals([
            'first_published' => 'date',
            'last_published' => 'date',
            'url' => 'url',
        ], (new DocumentStub)->getCasts());
    }
}
