<?php

namespace WebHappens\Prismic\Contracts\Fields;

interface DateHtmlSerializer
{
    public function serialize($date): string;
}
