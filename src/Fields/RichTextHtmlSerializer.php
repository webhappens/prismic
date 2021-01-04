<?php

namespace WebHappens\Prismic\Fields;

use Illuminate\Support\Str;
use WebHappens\Prismic\Contracts\Fields\RichTextHtmlSerializer as Contract;

class RichTextHtmlSerializer implements Contract
{
    protected $serializers = [];
    protected $inlineOnly = false;

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
        $type = $element->type;

        if ($this->inlineOnly && ! $this->isInlineElement($type)) {
            return $content;
        }

        if ($this->hasSerializerFor($type)) {
            return call_user_func($this->getSerializerFor($type), $element, $content);
        }

        $localMethod = 'serialize'.Str::studly($type);
        if (method_exists($this, $localMethod)) {
            return $this->$localMethod($element, $content);
        }

        return '';
    }

    public function __invoke($element, $content): string
    {
        return $this->serialize($element, $content);
    }

    public function inlineOnly($inlineOnly = true)
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
