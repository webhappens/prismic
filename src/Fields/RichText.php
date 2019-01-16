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

    public static function make(...$args): RichText
    {
        return new static(...$args);
    }

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function toHtml(): HtmlString
    {
        return new HtmlString($this->asHtml());
    }

    public function __toString()
    {
        return $this->asText();
    }

    protected function asText(): string
    {
        return trim(PrismicRichText::asText($this->data));
    }

    protected function asHtml(): string
    {
        return PrismicRichText::asHtml($this->data, resolve(DocumentUrlResolver::class), resolve(RichTextHtmlSerializer::class));
    }
}
