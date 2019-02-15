<?php

namespace WebHappens\Prismic\Tests\Stubs;

use WebHappens\Prismic\Slice;
use Illuminate\Support\HtmlString;

class SliceAStub extends Slice
{
    protected static $type = 'slice_a';

    public function toHtml(): HtmlString
    {
        return new HtmlString('<div>Slice A</div>');
    }

    public function getFoo()
    {
        return $this->data('foo');
    }
}
