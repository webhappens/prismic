<?php

namespace WebHappens\Prismic\Tests;

use WebHappens\Prismic\Fields\DocumentLink;
use WebHappens\Prismic\Tests\Stubs\DocumentAStub;

class DocumentLinkFieldTest extends LinkFieldTest
{
    public function test_make()
    {
        $document = $this->documentStub();
        $link = DocumentLink::make($document);
        $this->assertInstanceOf(DocumentLink::class, $link);
        $this->assertEquals($document->url, $link->getUrl());
        $this->assertEquals($document->title, $link->getTitle());
    }

    public function test_make_title_override()
    {
        $document = $this->documentStub();
        $link = DocumentLink::make($document, 'Override Title');
        $this->assertInstanceOf(DocumentLink::class, $link);
        $this->assertEquals('Override Title', $link->getTitle());
    }

    public function test_get_document()
    {
        $this->assertInstanceOf(
            DocumentAStub::class,
            $this->documentLink()->getDocument()
        );
    }

    public function test_open_in_new_tab()
    {
        $this->assertCanOpenInNewTab($this->documentLink());
    }

    public function test_attributes()
    {
        $this->assertCanSetAttributes($this->documentLink());
    }

    public function test_to_string()
    {
        $this->assertCanToString($this->documentLink());
    }

    public function test_to_html()
    {
        $this->assertCanToHtml(
            $this->documentLink()->attributes(['class' => 'foo'])
        );
    }

    protected function documentLink()
    {
        return DocumentLink::make($this->documentStub());
    }

    protected function documentStub()
    {
        $document = DocumentAStub::make();
        $document->url = 'https://example.org';
        $document->title = 'Example Title';

        return $document;
    }
}
