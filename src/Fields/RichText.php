<?php

namespace WebHappens\Prismic\Fields;

use Illuminate\Contracts\Support\Htmlable;
use Prismic\Dom\RichText as PrismicRichText;
use WebHappens\Prismic\Contracts\Fields\RichTextHtmlSerializer;
use WebHappens\Prismic\DocumentUrlResolver;

class RichText implements Htmlable
{
    protected $data;
    protected $richTextHtmlSerializer;

    public static function make(...$parameters): self
    {
        return new static(...$parameters);
    }

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function setRichTextHtmlSerializer(RichTextHtmlSerializer $richTextHtmlSerializer)
    {
        $this->richTextHtmlSerializer = $richTextHtmlSerializer;

        return $this;
    }

    public function getRichTextHtmlSerializer()
    {
        if (is_null($this->richTextHtmlSerializer)) {
            return resolve(RichTextHtmlSerializer::class);
        }

        return $this->richTextHtmlSerializer;
    }

    public function asText(): string
    {
        return $this->__toString();
    }

    public function toHtml()
    {
        return PrismicRichText::asHtml(
            $this->data,
            resolve(DocumentUrlResolver::class),
            $this->getRichTextHtmlSerializer()
        );
    }

    public function __toString()
    {
        return trim(PrismicRichText::asText($this->data));
    }
}
