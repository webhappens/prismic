<?php

namespace WebHappens\Prismic\Fields;

use WebHappens\Prismic\Contracts\Fields\LinkHtmlSerializer as Contract;

class LinkHtmlSerializer implements Contract
{
    public function serialize($link): string
    {
        $attributes = '';

        foreach ($link->getAttributes() as $key => $value) {
            $attributes .= ' '.$key.'="'.$value.'"';
        }

        return sprintf(
            '<a href="%s"%s>%s</a>',
            $link->getUrl(),
            $attributes,
            $link->getTitle()
        );
    }
}
