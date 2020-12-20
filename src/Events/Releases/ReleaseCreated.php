<?php

namespace WebHappens\Prismic\Events\Releases;

class ReleaseCreated
{
    public $release;

    public function __construct($release)
    {
        $this->release = $release;
    }
}
