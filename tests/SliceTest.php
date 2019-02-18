<?php

namespace WebHappens\Prismic\Tests;

use WebHappens\Prismic\Slice;
use WebHappens\Prismic\Prismic;
use WebHappens\Prismic\Tests\Stubs\SliceAStub;
use WebHappens\Prismic\Tests\Stubs\SliceBStub;

class SliceTest extends TestCase
{
    public function test_get_type()
    {
        $this->assertEquals('slice_a', SliceAStub::getType());
    }

    public function test_resolve_class_from_type()
    {
        Prismic::slices([SliceAStub::class, SliceBStub::class]);
        $this->assertEquals(SliceAStub::class, Slice::resolveClassFromType('slice_a'));
        $this->assertEquals(SliceBStub::class, Slice::resolveClassFromType('slice_b'));
        Prismic::$slices = [];
    }

    public function test_make()
    {
        $this->assertInstanceOf(SliceAStub::class, SliceAStub::make([]));
    }

    public function test_using_can_chain()
    {
        $this->assertInstanceOf(SliceAStub::class, (new SliceAStub([]))->using('foo'));
    }

    public function test_view_name_default()
    {
        $this->assertEquals('slices.slice_a', (new SliceAStub([]))->viewName());
    }

    public function test_using_changes_view_name()
    {
        $this->assertEquals('slice_a_alt', (new SliceAStub([]))->using('slice_a_alt')->viewName());
    }

    public function test_data()
    {
        $this->assertEquals('bar', (new SliceAStub(['foo' => 'bar']))->getFoo());
    }
}