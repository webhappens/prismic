<?php

namespace WebHappens\Prismic\Tests;

use Hamcrest\Matchers;
use Illuminate\Http\RedirectResponse;
use Mockery as m;
use Prismic\Api;
use WebHappens\Prismic\DocumentUrlResolver;
use WebHappens\Prismic\Prismic;

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

    public function test_can_chain_from_static()
    {
        $prismic = Prismic::documents([]);
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
