<?php

namespace WebHappens\Prismic\Tests\Stubs;

use WebHappens\Prismic\Slice;

class SliceAStub extends Slice
{
    protected static $type = 'slice_a';

    public function toHtml()
    {
        return '';
    }

    public function getFoo()
    {
        return $this->data('foo');
    }
}
