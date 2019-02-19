<?php

namespace WebHappens\Prismic\Tests\Stubs;

use WebHappens\Prismic\Slice;
use Illuminate\Support\HtmlString;

class SliceBStub extends Slice
{
    protected static $type = 'slice_b';

    public function toHtml(): HtmlString
    {
        return new HtmlString();
    }
}
