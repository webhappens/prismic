<?php

namespace WebHappens\Prismic\Fields;

use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Htmlable;
use WebHappens\Prismic\DocumentUrlResolver;
use Prismic\Dom\RichText as PrismicRichText;
use WebHappens\Prismic\Contracts\Fields\RichTextHtmlSerializer;

class RichText implements Htmlable
{
    protected $data;

    public static function make(...$parameters): RichText
    {
        return new static(...$parameters);
    }

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function asText(): string
    {
        return $this->__toString();
    }

    public function toHtml(): HtmlString
    {
        return new HtmlString(
            PrismicRichText::asHtml(
                $this->data,
                resolve(DocumentUrlResolver::class),
                resolve(RichTextHtmlSerializer::class)
            )
        );
    }

    public function __toString()
    {
        return trim(PrismicRichText::asText($this->data));
    }
}
