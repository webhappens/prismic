<?php

namespace WebHappens\Prismic\Events\Releases;

class ReleaseDeleted
{
    public $release;

    public function __construct($release)
    {
        $this->release = $release;
    }
}
