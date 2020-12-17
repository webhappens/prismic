<?php

namespace WebHappens\Prismic\Tests\Query;

use Mockery;
use Prismic\Api;
use InvalidArgumentException;
use WebHappens\Prismic\Query;
use WebHappens\Prismic\Prismic;
use Illuminate\Support\Collection;
use WebHappens\Prismic\Tests\TestCase;
use WebHappens\Prismic\Tests\Stubs\DocumentAStub;
use WebHappens\Prismic\Tests\Stubs\DocumentBStub;

class RetrievalMethodsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Prismic::documents([DocumentAStub::class, DocumentBStub::class]);
    }

    public function test_find()
    {
        $this->assertNull(Query::make()->find(null));

        $this->swap(Api::class, Mockery::mock(Api::class, function($mock) {
            $mock->shouldReceive('query')
                ->andReturn((object) [
                    'total_pages' => 1,
                    'results' => [
                        (object) ['id' => '1', 'type' => 'document_a'],
                    ],
                ]);
        }));

        $this->assertInstanceOf(DocumentAStub::class, Query::make()->find(5));
    }

    public function test_find_many()
    {
        $results = Query::make()->findMany([]);
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEmpty($results);

        $this->swap(Api::class, Mockery::mock(Api::class, function($mock) {
            $mock->shouldReceive('query')
                ->andReturn((object) [
                    'total_pages' => 1,
                    'results' => [
                        (object) ['id' => '1', 'type' => 'document_a'],
                        (object) ['id' => '2', 'type' => 'document_a'],
                        (object) ['id' => '5', 'type' => 'document_b'],
                    ],
                ]);
        }));

        $results = Query::make()->findMany([1, 2, 5]);
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(DocumentAStub::class, $results[0]);
        $this->assertInstanceOf(DocumentAStub::class, $results[1]);
        $this->assertInstanceOf(DocumentBStub::class, $results[2]);
    }

    public function test_single()
    {
        $this->assertNull(Query::make()->single());

        $this->swap(Api::class, $this->partialMock(Api::class, function($mock) {
            $mock->shouldReceive('query')
                ->andReturn((object) [
                    'results' => [
                        (object) ['id' => '1', 'type' => 'document_a'],
                        (object) ['id' => '2', 'type' => 'document_a'],
                    ],
                ]);
        }));

        $result = Query::make()->type('document_a')->single();
        $this->assertInstanceOf(DocumentAStub::class, $result);
        $this->assertEquals(1, $result->id);

        $result = Query::make()->single('document_a');
        $this->assertInstanceOf(DocumentAStub::class, $result);
        $this->assertEquals(1, $result->id);
    }

    public function test_get_raw()
    {
        $rawResponse = (object) [
            'total_pages' => 1,
            'results' => [
                (object) ['id' => '1', 'type' => 'document_a'],
                (object) ['id' => '2', 'type' => 'document_a'],
                (object) ['id' => '3', 'type' => 'document_b'],
            ],
        ];

        $this->swap(Api::class, $this->partialMock(Api::class, function($mock) use ($rawResponse) {
            $mock->shouldReceive('query')
                ->andReturn($rawResponse);
        }));

        $results = Query::make()->getRaw();
        $this->assertEquals($rawResponse, $results);
    }

    public function test_get()
    {
        $this->swap(Api::class, $this->partialMock(Api::class, function($mock) {
            $mock->shouldReceive('query')
                ->andReturn((object) [
                    'total_pages' => 1,
                    'results' => [
                        (object) ['id' => '1', 'type' => 'document_a'],
                        (object) ['id' => '2', 'type' => 'document_a'],
                        (object) ['id' => '3', 'type' => 'document_b'],
                    ],
                ]);
        }));

        $results = Query::make()->get();
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(DocumentAStub::class, $results[0]);
        $this->assertInstanceOf(DocumentAStub::class, $results[1]);
        $this->assertInstanceOf(DocumentBStub::class, $results[2]);
    }

    public function test_first()
    {
        $this->swap(Api::class, $this->partialMock(Api::class, function($mock) {
            $mock->shouldReceive('query')
                ->andReturn((object) [
                    'total_pages' => 1,
                    'results' => [
                        (object) ['id' => '1', 'type' => 'document_a'],
                        (object) ['id' => '2', 'type' => 'document_a'],
                        (object) ['id' => '3', 'type' => 'document_b'],
                    ],
                ]);
        }));

        $result = Query::make()->first();
        $this->assertInstanceOf(DocumentAStub::class, $result);
        $this->assertEquals(1, $result->id);
    }


    public function test_chunk()
    {
        $this->swap(Api::class, Mockery::mock(Api::class, function($mock) {
            $mock->shouldReceive('query')
                ->times(3)
                ->with([], Mockery::anyOf(
                    ['pageSize' => 2, 'page' => 1],
                    ['pageSize' => 2, 'page' => 2],
                    ['pageSize' => 2, 'page' => 3]
                ))
                ->andReturn(
                    (object) [
                        'page' => 1,
                        'total_pages' => 3,
                        'total_results_size' => 5,
                        'results' => [
                            (object) ['id' => '1', 'type' => 'document_a'],
                            (object) ['id' => '2', 'type' => 'document_a'],
                        ],
                    ],
                    (object) [
                        'page' => 2,
                        'total_pages' => 3,
                        'total_results_size' => 5,
                        'results' => [
                            (object) ['id' => '3', 'type' => 'document_a'],
                            (object) ['id' => '4', 'type' => 'document_b'],
                        ],
                    ],
                    (object) [
                        'page' => 3,
                        'total_pages' => 3,
                        'total_results_size' => 5,
                        'results' => [
                            (object) ['id' => '5', 'type' => 'document_b'],
                        ],
                    ]
                );
        }));

        $documents = collect();
        Query::make()->chunk(2, function ($chunk) use ($documents) {
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
        $this->expectExceptionMessage('The maximum chunk limit allowed by Prismic is 100');

        Query::make()->chunk(101, function () {
            // do nothing
        });
    }

    public function test_count()
    {
        $this->swap(Api::class, $this->partialMock(Api::class, function($mock) {
            $mock->shouldReceive('query')
                ->andReturn((object) [
                    'total_results_size' => 17
                ]);
        }));

        $this->assertEquals(17, Query::make()->count());
    }
}
