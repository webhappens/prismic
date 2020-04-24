<?php

namespace WebHappens\Prismic\Facades;

use WebHappens\Prismic\Relation as R;
use Illuminate\Support\Facades\Facade;

class Relation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return R::class;
    }
}
