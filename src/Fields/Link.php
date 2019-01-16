<?php

namespace WebHappens\Prismic\Fields;

use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Htmlable;
use WebHappens\Prismic\Contracts\Linkable;
use WebHappens\Prismic\Fields\LinkResolver;
use WebHappens\Prismic\Contracts\Fields\LinkHtmlSerializer;

abstract class Link implements Linkable, Htmlable
{
    protected $url;
    protected $title;
    protected $attributes = [];

    public static function resolve(...$args): ?Link
    {
        return resolve(LinkResolver::class)->resolve(...$args);
    }

    public static function make(...$args): Link
    {
        return new static(...$args);
    }

    public function __construct($url, $title = null)
    {
        $this->url = $url;
        $this->title = trim($title) ?: null;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function attributes(array $attributes): Link
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function toHtml(): HtmlString
    {
        return new HtmlString(
            (resolve(LinkHtmlSerializer::class))->serialize($this)
        );
    }

    public function __toString()
    {
        return $this->getUrl();
    }
}
