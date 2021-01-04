<?php

namespace WebHappens\Prismic\Tests;

use Mockery;
use WebHappens\Prismic\Fields\Link;
use WebHappens\Prismic\Fields\LinkResolver;

class LinkField extends TestCase
{
    public function test_resolve()
    {
        $linkResolver = Mockery::mock(LinkResolver::class)->makePartial();
        $linkResolver->shouldReceive('resolve')->once()->with('foo')->andReturn(null);
        $this->swap(LinkResolver::class, $linkResolver);
        $this->assertEquals(Link::resolve('foo'), null);
    }

    public function assertCanOpenInNewTab($link)
    {
        $this->assertEmpty($link->getAttributes());

        $link->openInNewTab();
        $linkAttributes = $link->getAttributes();
        $this->assertArrayHasKey('target', $linkAttributes);
        $this->assertSame('_blank', $linkAttributes['target']);

        $link->openInNewTab(false);
        $this->assertEmpty($link->getAttributes());

        $link->openInNewTab(true);
        $linkAttributes = $link->getAttributes();
        $this->assertArrayHasKey('target', $linkAttributes);
        $this->assertSame('_blank', $linkAttributes['target']);
    }

    public function assertCanSetAttributes($link)
    {
        $link
            ->attributes(['a' => 'a', 'b' => 'b'])
            ->attributes(['c' => 'c']);

        $this->assertEquals(
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            $link->getAttributes()
        );
    }

    public function assertCanToString($link)
    {
        $this->assertEquals($link->getUrl(), (string) $link);
    }

    public function assertCanToHtml($link)
    {
        $this->assertEquals(
            '<a href="https://example.org" class="foo">Example Title</a>',
            $link->toHtml()
        );
    }
}
