<?php

namespace WebHappens\Prismic\Events;

class MasterRefChanged
{
    public $ref;

    public function __construct($ref)
    {
        $this->ref = $ref;
    }
}
