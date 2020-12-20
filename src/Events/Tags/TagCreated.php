<?php

namespace WebHappens\Prismic\Events\Tags;

class TagCreated
{
    public $tag;

    public function __construct($tag)
    {
        $this->tag = $tag;
    }
}
