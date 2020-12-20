<?php

namespace WebHappens\Prismic\Events\Tags;

class TagDeleted
{
    public $tag;

    public function __construct($tag)
    {
        $this->tag = $tag;
    }
}
