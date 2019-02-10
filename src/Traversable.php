<?php

namespace WebHappens\Prismic;

trait Traversable
{
    public function parent()
    {
        return $this->traverse()->parent();
    }

    public function children()
    {
        return $this->traverse()->children();
    }
}