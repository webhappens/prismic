<?php

namespace WebHappens\Prismic\Fields;

use Illuminate\Contracts\Support\Htmlable;
use WebHappens\Prismic\DocumentUrlResolver;
use Prismic\Dom\RichText as PrismicRichText;
use WebHappens\Prismic\Contracts\Fields\RichTextHtmlSerializer;

class RichText implements Htmlable
{
    protected $data;
    protected $htmlSerializer;

    public static function make(...$parameters): self
    {
        return new static(...$parameters);
    }

    public function __construct($data = null)
    {
        if (is_string($data) && trim($data)) {
            $this->data = [
                (object) [
                    'type' => 'paragraph',
                    'text' => trim($data),
                    'spans' => [],
                ],
            ];
        } else if (is_array($data) && ! $this->isBlank($data)) {
            $this->data = $data;
        }

        $this->htmlSerializer = resolve(RichTextHtmlSerializer::class);
    }

    public function heading1(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('heading1', $serializer);

        return $this;
    }

    public function heading2(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('heading2', $serializer);

        return $this;
    }

    public function heading3(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('heading3', $serializer);

        return $this;
    }

    public function heading4(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('heading4', $serializer);

        return $this;
    }

    public function heading5(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('heading5', $serializer);

        return $this;
    }

    public function heading6(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('heading6', $serializer);

        return $this;
    }

    public function paragraph(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('paragraph', $serializer);

        return $this;
    }

    public function preformatted(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('preformatted', $serializer);

        return $this;
    }

    public function listItem(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('list-item', $serializer);

        return $this;
    }

    public function orderedListItem(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('o-list-item', $serializer);

        return $this;
    }

    public function image(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('image', $serializer);

        return $this;
    }

    public function embed(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('embed', $serializer);

        return $this;
    }

    public function strong(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('strong', $serializer);

        return $this;
    }

    public function em(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('em', $serializer);

        return $this;
    }

    public function hyperlink(callable $serializer)
    {
        $this->htmlSerializer->registerSerializerFor('hyperlink', $serializer);

        return $this;
    }

    public function inlineOnly($inlineOnly = true)
    {
        if ($this->htmlSerializer) {
            $this->htmlSerializer->inlineOnly($inlineOnly);
        }

        return $this;
    }

    public function setHtmlSerializer(RichTextHtmlSerializer $htmlSerializer)
    {
        $this->htmlSerializer = $htmlSerializer;

        return $this;
    }

    public function getHtmlSerializer()
    {
        return $this->htmlSerializer;
    }

    public function asText(): string
    {
        return $this->__toString();
    }

    public function toHtml()
    {
        if ( ! $this->data) {
            return '';
        }

        return PrismicRichText::asHtml(
            $this->data,
            resolve(DocumentUrlResolver::class),
            $this->getHtmlSerializer()
        );
    }

    public function __toString()
    {
        if ( ! $this->data) {
            return '';
        }

        return trim(PrismicRichText::asText($this->data));
    }

    protected function isBlank($data)
    {
        $data = array_filter($data, function($item) {
            return array_filter((array) $item) !== ['type' => 'paragraph'];
        });

        return count($data) === 0;
    }

}
