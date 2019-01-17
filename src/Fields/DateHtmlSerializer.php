<?php

namespace WebHappens\Prismic\Fields;

use WebHappens\Prismic\Contracts\Fields\DateHtmlSerializer as Contract;

class DateHtmlSerializer implements Contract
{
    public function serialize($date): string
    {
        return sprintf(
            '<time datetime="%s">%s</time>',
            $date->format('Y-m-d H:i'),
            $date
        );
    }
}
