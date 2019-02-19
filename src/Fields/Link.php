<?php

namespace WebHappens\Prismic\Fields;

use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Htmlable;
use WebHappens\Prismic\Fields\LinkResolver;
use WebHappens\Prismic\Contracts\Fields\LinkHtmlSerializer;

abstract class Link implements Htmlable
{
    protected $url;
    protected $title;
    protected $attributes = [];

    public static function resolve(...$parameters): ?Link
    {
        return resolve(LinkResolver::class)->resolve(...$parameters);
    }

    public static function make(...$parameters): Link
    {
        return new static(...$parameters);
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

    public function openInNewTab(bool $bool = true): Link
    {
        if ($bool) {
            $this->attributes(['target' => '_blank']);
        } else {
            unset($this->attributes['target']);
        }

        return $this;
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
