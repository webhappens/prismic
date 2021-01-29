<?php

namespace WebHappens\Prismic\Fields;

use Illuminate\Support\Str;
use WebHappens\Prismic\Fields\LinkResolver;
use WebHappens\Prismic\Contracts\Fields\RichTextHtmlSerializer as Contract;

class RichTextHtmlSerializer implements Contract
{
    protected $serializers = [];
    protected $inlineOnly = false;
    protected $shiftHeadings = 0;

    public function registerSerializerFor(string $type, callable $serializer): Contract
    {
        unset($this->serializers[$type]);

        $this->serializers[$type] = $serializer;

        return $this;
    }

    public function hasSerializerFor(string $type): bool
    {
        return array_key_exists($type, $this->serializers);
    }

    public function getSerializerFor(string $type): ?callable
    {
        return $this->serializers[$type] ?? null;
    }

    public function serialize($element, $content): string
    {
        if ($this->inlineOnly && ! $this->isInlineElement($element->type)) {
            return $content;
        }

        if ($this->shiftHeadings && Str::startsWith($element->type, 'heading')) {
            $newHeadingLevel = str_replace('heading', '', $element->type) + $this->shiftHeadings;

            if ($newHeadingLevel < 1) {
                $newHeadingLevel = 1;
            } else if ($newHeadingLevel > 6) {
                $newHeadingLevel = 6;
            }

            $element->type = "heading$newHeadingLevel";
        }

        if ($this->hasSerializerFor($element->type)) {
            return call_user_func($this->getSerializerFor($element->type), $element, $content);
        }

        $localMethod = 'serialize'.Str::studly($element->type);
        if (method_exists($this, $localMethod)) {
            return $this->$localMethod($element, $content);
        }

        return '';
    }

    public function serializeHyperlink($element, $content)
    {
        return (string) (new LinkResolver)->resolve($element->data, $content)->toHtml();
    }

    public function __invoke($element, $content): string
    {
        return $this->serialize($element, $content);
    }

    public function shiftHeadings(int $shiftBy = 0): Contract
    {
        $this->shiftHeadings = $shiftBy;

        return $this;
    }

    public function inlineOnly(bool $inlineOnly = true): Contract
    {
        $this->inlineOnly = $inlineOnly;

        return $this;
    }

    public function isInlineElement($type): bool
    {
        return in_array($type, [
            'strong',
            'em',
            'hyperlink',
        ]);
    }
}
