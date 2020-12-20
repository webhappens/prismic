<?php

namespace WebHappens\Prismic\Events\Releases;

class ReleaseUpdated
{
    public $release;

    public function __construct($release)
    {
        $this->release = $release;
    }
}
