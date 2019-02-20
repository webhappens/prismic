<?php

namespace WebHappens\Prismic\Tests\Stubs;

use WebHappens\Prismic\Slice;

class SliceBStub extends Slice
{
    protected static $type = 'slice_b';

    public function toHtml()
    {
        return '';
    }
}
