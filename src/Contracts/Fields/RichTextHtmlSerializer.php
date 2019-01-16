<?php

namespace WebHappens\Prismic\Contracts\Fields;

interface RichTextHtmlSerializer
{
    public function serialize($element, $content): string;
    public function __invoke($element, $content): string;
}
