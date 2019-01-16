<?php

namespace WebHappens\Prismic\Contracts\Fields;

interface LinkHtmlSerializer
{
    public function serialize($link): string;
}
