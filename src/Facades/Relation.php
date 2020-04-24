<?php

namespace WebHappens\Prismic\Facades;

use Illuminate\Support\Facades\Facade;
use WebHappens\Prismic\Relation as R;

class Relation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return R::class;
    }
}
