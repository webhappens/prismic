<?php

namespace WebHappens\Prismic\Tests;

use Prismic\Api;
use Mockery as m;
use Hamcrest\Matchers;
use WebHappens\Prismic\Prismic;
use Illuminate\Http\RedirectResponse;
use WebHappens\Prismic\DocumentUrlResolver;

class PrismicTest extends TestCase
{
    public function test_documents()
    {
        $documents = [
            'App\Article',
            'App\Collection',
        ];

        Prismic::documents($documents);
        $this->assertEquals($documents, Prismic::$documents);
        Prismic::$documents = [];
    }

    public function test_slices()
    {
        $slices = [
            'App\Slices\RichText',
            'App\Slices\Table',
        ];

        Prismic::slices($slices);
        $this->assertEquals($slices, Prismic::$slices);
        Prismic::$slices = [];
    }

    public function test_can_chain_from_static()
    {
        $prismic = Prismic::documents([])->slices([]);
        $this->assertInstanceOf(Prismic::class, $prismic);
    }

    public function test_preview()
    {
        $token = 'my-token';
        $documentUrlResolver = Matchers::equalTo(new DocumentUrlResolver);
        $url = 'https://example.org';

        $api = m::mock(Api::class);
        $api->shouldReceive('previewSession')
            ->once()
            ->with($token, $documentUrlResolver, '/')
            ->andReturn($url);
        $this->swap(Api::class, $api);

        $redirect = Prismic::preview($token);
        $this->assertInstanceOf(RedirectResponse::class, $redirect);
        $this->assertEquals($url, $redirect->getTargetUrl());
    }
}
