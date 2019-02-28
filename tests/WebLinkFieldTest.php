<?php

namespace WebHappens\Prismic\Tests;

use WebHappens\Prismic\Fields\WebLink;

class WebLinkFieldTest extends LinkField
{
    public function test_make()
    {
        $link = $this->webLink();
        $this->assertInstanceOf(WebLink::class, $link);
        $this->assertEquals('https://example.org', $link->getUrl());
        $this->assertEquals('Example Title', $link->getTitle());
    }

    public function test_fragment_only()
    {
        // Prismic forces a scheme to all links

        $link = WebLink::make('https://#fragment');
        $this->assertEquals('#fragment', $link->getUrl());

        $link = WebLink::make('http://#fragment');
        $this->assertEquals('#fragment', $link->getUrl());
    }

    public function test_open_in_new_tab()
    {
        $this->assertCanOpenInNewTab($this->webLink());
    }

    public function test_attributes()
    {
        $this->assertCanSetAttributes($this->webLink());
    }

    public function test_to_string()
    {
        $this->assertCanToString($this->webLink());
    }

    public function test_to_html()
    {
        $this->assertCanToHtml(
            $this->webLink()->attributes(['class' => 'foo'])
        );
    }

    protected function webLink()
    {
        return WebLink::make('https://example.org', 'Example Title');
    }
}
