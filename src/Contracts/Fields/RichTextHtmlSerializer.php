<?php

namespace WebHappens\Prismic\Contracts\Fields;

interface RichTextHtmlSerializer
{
    public function registerSerializerFor(string $type, callable $serializer): self;

    public function hasSerializerFor(string $type): bool;

    public function serialize($element, $content): string;

    public function __invoke($element, $content): string;
}
