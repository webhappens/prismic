<?php

namespace WebHappens\Prismic\Tests;

use WebHappens\Prismic\Fields\Link;
use WebHappens\Prismic\Fields\RichText;
use WebHappens\Prismic\Fields\LinkResolver;
use WebHappens\Prismic\Fields\RichTextHtmlSerializer;

class RichTextHtmlSerializerTest extends TestCase
{
    public function test_standard_serialization()
    {
        $richtext = RichText::make([(object) [
                "type" => "heading3",
                "text" => "Heading 3",
                "spans" => [],
            ]])
            ->setHtmlSerializer(new RichTextHtmlSerializer);

        $this->assertEquals('<h3>Heading 3</h3>', $richtext->toHtml());
        $this->assertEquals('Heading 3', $richtext->asText());

        $richtext = RichText::make([(object) [
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
            ]])
            ->setHtmlSerializer(new RichTextHtmlSerializer);

        $this->assertEquals('<p>Look at <a href="http://helpcentre.test/design-guide/components/text#">past papers</a>, so you know what to expect.</p>', $richtext->toHtml());
        $this->assertEquals('Look at past papers, so you know what to expect.', $richtext->asText());
    }

    public function test_class_method_overrides()
    {
        $extendedSerializer = new class extends RichTextHtmlSerializer
        {
            public function serializeHeading3($element)
            {
                return '<h3 class="text-red">' . $element->text . '</h3>';
            }

        };

        $richtext = RichText::make([(object) [
                "type" => "heading3",
                "text" => "Heading 3",
                "spans" => [],
            ]])
            ->setHtmlSerializer($extendedSerializer);

        $this->assertEquals('<h3 class="text-red">Heading 3</h3>', $richtext->toHtml());
        $this->assertEquals('Heading 3', $richtext->asText());
    }

    public function test_instance_based_overrides()
    {
        $extendedSerializer = new class extends RichTextHtmlSerializer
        {
            public function serializeHeading3($element)
            {
                return '<h3 class="text-red">' . $element->text . '</h3>';
            }

        };

        $richtext = RichText::make([(object) [
                "type" => "heading3",
                "text" => "Heading 3",
                "spans" => [],
            ]])
            ->setHtmlSerializer($extendedSerializer)
            ->heading3(function($element) {
                return '<h3 class="text-green">' . $element->text . '</h3>';
            });

        $this->assertEquals('<h3 class="text-green">Heading 3</h3>', $richtext->toHtml());
        $this->assertEquals('Heading 3', $richtext->asText());
    }

    public function test_span_override()
    {
        $richtext = RichText::make([(object) [
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
        ]])
        ->setHtmlSerializer(new RichTextHtmlSerializer)
        ->hyperlink(function($element, $content) {
            return Link::resolve($element->data, $content)->attributes(['class' => 'jazzy'])->toHtml();
        });

        $this->assertEquals('<p>Look at <a href="http://helpcentre.test/design-guide/components/text#" class="jazzy">past papers</a>, so you know what to expect.</p>', $richtext->toHtml());
        $this->assertEquals('Look at past papers, so you know what to expect.', $richtext->asText());
    }
}
