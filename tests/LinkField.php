<?php

namespace WebHappens\Prismic\Tests;

use Mockery as m;
use WebHappens\Prismic\Fields\Link;
use WebHappens\Prismic\Fields\LinkResolver;

class LinkField extends TestCase
{
    public function test_resolve()
    {
        $linkResolver = m::mock(LinkResolver::class . '[resolve]');
        $linkResolver->shouldReceive('resolve')->once()->with('foo')->andReturn(null);
        $this->swap(LinkResolver::class, $linkResolver);
        $this->assertEquals(Link::resolve('foo'), null);
    }

    public function assertCanOpenInNewTab($link)
    {
        $this->assertEmpty($link->getAttributes());
        $link->openInNewTab();
        $this->assertArraySubset(['target' => '_blank'], $link->getAttributes());
        $link->openInNewTab(false);
        $this->assertEmpty($link->getAttributes());
        $link->openInNewTab(true);
        $this->assertArraySubset(['target' => '_blank'], $link->getAttributes());
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
