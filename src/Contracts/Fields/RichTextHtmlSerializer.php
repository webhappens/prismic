<?php

namespace WebHappens\Prismic\Contracts\Fields;

interface RichTextHtmlSerializer
{
    public function registerSerializerFor(string $type, callable $serializer): self;

    public function hasSerializerFor(string $type): bool;

    public function getSerializerFor(string $type): ?callable;

    public function serialize($element, $content): string;

    public function shiftHeadings(int $shiftBy): self;

    public function inlineOnly(bool $inlineOnly): self;

    public function __invoke($element, $content): string;
}
