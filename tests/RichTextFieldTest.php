<?php

namespace WebHappens\Prismic\Tests;

use Faker\Provider\ar_JO\Text;
use WebHappens\Prismic\Fields\RichText;

class RichTextFieldTest extends TestCase
{
    public function test_make()
    {
        $richtext = RichText::make();
        $this->assertInstanceOf(RichText::class, $richtext);
        $this->assertEquals('', $richtext->toHtml());
        $this->assertEquals('', $richtext->asText());
        $this->assertEquals('', (string) $richtext);

        $richtext = new RichText;
        $this->assertInstanceOf(RichText::class, $richtext);
        $this->assertEquals('', $richtext->toHtml());
        $this->assertEquals('', $richtext->asText());
        $this->assertEquals('', (string) $richtext);
    }

    public function test_can_instantiate_with_string()
    {
        $richtext = RichText::make('This makes instantiating with a KeyText field really easy.');
        $this->assertInstanceOf(RichText::class, $richtext);
        $this->assertEquals('<p>This makes instantiating with a KeyText field really easy.</p>', $richtext->toHtml());
        $this->assertEquals('This makes instantiating with a KeyText field really easy.', $richtext->asText());
        $this->assertEquals('This makes instantiating with a KeyText field really easy.', (string) $richtext);
    }

    public function test_can_instantiate_with_prismic_data()
    {
        $richtext = RichText::make([
            (object) [
                "type" => "heading3",
                "text" => "Heading 3",
                "spans" => [],
            ],
            (object) [
                "type" => "heading4",
                "text" => "Heading 4",
                "spans" => [],
            ],
            (object) [
                "type" => "paragraph",
                "text" => "This line rendered as normal text.",
                "spans" => [],
            ],
            (object) [
                "type" => "paragraph",
                "text" => "This line rendered as bold text.",
                "spans" => [
                    (object) [
                        "start" => 0,
                        "end" => 32,
                        "type" => "strong",
                    ],
                ],
            ],
            (object) [
                "type" => "paragraph",
                "text" => "This line rendered as italicised text.",
                "spans" => [
                    (object) [
                        "start" => 0,
                        "end" => 38,
                        "type" => "em",
                    ],
                ],
            ],
            (object) [
                "type" => "paragraph",
                "text" => "Look at past papers, so you know what to expect.",
                "spans" => [
                    (object) [
                        "start" => 8,
                        "end" => 19,
                        "type" => "hyperlink",
                        "data" => (object) [
                            "link_type" => "Web",
                            "url" => "http://helpcentre.test/design-guide/components/text#",
                        ],
                    ],
                ],
            ],
            (object) [
                "type" => "paragraph",
                "text" => "Download your module results",
                "spans" => [
                    (object) [
                        "start" => 14,
                        "end" => 28,
                        "type" => "hyperlink",
                        "data" => (object) [
                            "link_type" => "Media",
                            "name" => "certificate-in-advanced-prof-practice-two-pg-2018-19.pdf",
                            "kind" => "document",
                            "url" => "https://ouhelpcentre.cdn.prismic.io/ouhelpcentre/b0ac0d63-18e2-4dc9-a26a-7d9467ac6ba4_certificate-in-advanced-prof-practice-two-pg-2018-19.pdf",
                            "size" => "145615",
                        ],
                    ],
                ],
            ],
            (object) [
                "type" => "list-item",
                "text" => "List item one",
                "spans" => [],
            ],
            (object) [
                "type" => "list-item",
                "text" => "List item two",
                "spans" => [],
            ],
            (object) [
                "type" => "list-item",
                "text" => "List item three",
                "spans" => [],
            ],
            (object) [
                "type" => "list-item",
                "text" => "List item four",
                "spans" => [],
            ],
            (object) [
                "type" => "o-list-item",
                "text" => "List item one",
                "spans" => [],
            ],
            (object) [
                "type" => "o-list-item",
                "text" => "List item two",
                "spans" => [],
            ],
            (object) [
                "type" => "o-list-item",
                "text" => "List item three",
                "spans" => [],
            ],
            (object) [
                "type" => "o-list-item",
                "text" => "List item four",
                "spans" => [],
            ],
        ]);

        $this->assertEquals(
            '<h3>Heading 3</h3>' .
            '<h4>Heading 4</h4>' .
            '<p>This line rendered as normal text.</p>' .
            '<p><strong>This line rendered as bold text.</strong></p>' .
            '<p><em>This line rendered as italicised text.</em></p>' .
            '<p>Look at <a href="http://helpcentre.test/design-guide/components/text#">past papers</a>, so you know what to expect.</p>' .
            '<p>Download your <a href="https://ouhelpcentre.cdn.prismic.io/ouhelpcentre/b0ac0d63-18e2-4dc9-a26a-7d9467ac6ba4_certificate-in-advanced-prof-practice-two-pg-2018-19.pdf">module results</a></p>' .
            '<ul>' .
                '<li>List item one</li>' .
                '<li>List item two</li>' .
                '<li>List item three</li>' .
                '<li>List item four</li>' .
            '</ul>' .
            '<ol>' .
                '<li>List item one</li>' .
                '<li>List item two</li>' .
                '<li>List item three</li>' .
                '<li>List item four</li>' .
            '</ol>', $richtext->toHtml());

        $this->assertEquals(
            'Heading 3' . "\n" .
            'Heading 4' . "\n" .
            'This line rendered as normal text.' . "\n" .
            'This line rendered as bold text.' . "\n" .
            'This line rendered as italicised text.' . "\n" .
            'Look at past papers, so you know what to expect.' . "\n" .
            'Download your module results' . "\n" .
            'List item one' . "\n" .
            'List item two' . "\n" .
            'List item three' . "\n" .
            'List item four' . "\n" .
            'List item one' . "\n" .
            'List item two' . "\n" .
            'List item three' . "\n" .
            'List item four', $richtext->asText());
    }

    public function test_blank_paragraph_with_no_text_renders_nothing()
    {
        $richtext = RichText::make([
            (object) [
                "type" => "paragraph",
                "text" => "",
                "spans" => [],
            ],
        ]);

        $this->assertInstanceOf(RichText::class, $richtext);
        $this->assertEquals('', $richtext->toHtml());
        $this->assertEquals('', $richtext->asText());
        $this->assertEquals('', (string) $richtext);
    }
}
