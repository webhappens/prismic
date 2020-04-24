<?php

namespace WebHappens\Prismic\Fields;

use WebHappens\Prismic\Contracts\Fields\RichTextHtmlSerializer as Contract;
use WebHappens\Prismic\Fields\LinkResolver;

class RichTextHtmlSerializer implements Contract
{
    public function serialize($element, $content): string
    {
        switch ($element->type) {
            case 'hyperlink':
                return (string) (new LinkResolver)->resolve($element->data, $content)->toHtml();
        }

        return '';
    }

    public function __invoke($element, $content): string
    {
        return $this->serialize($element, $content);
    }
}
