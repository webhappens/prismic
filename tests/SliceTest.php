<?php

namespace WebHappens\Prismic\Tests;

use WebHappens\Prismic\Slice;
use WebHappens\Prismic\Prismic;
use Facades\WebHappens\Prismic\SliceResolver;
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
        $this->assertInstanceOf(SliceAStub::class, SliceResolver::resolve('slice_a', []));
        $this->assertInstanceOf(SliceBStub::class, SliceResolver::resolve('slice_b', []));
        $this->assertInstanceOf(SliceAStub::class, SliceResolver::resolve(['slice_type' => 'slice_a']));
        $this->assertInstanceOf(SliceBStub::class, SliceResolver::resolve(['slice_type' => 'slice_b']));
        Prismic::$slices = [];
    }

    public function test_make()
    {
        $this->assertInstanceOf(SliceAStub::class, SliceAStub::make([]));
    }

    public function test_using_can_chain()
    {
        $this->assertInstanceOf(SliceAStub::class, SliceAStub::make([])->using('foo'));
    }

    public function test_view_name_default()
    {
        $this->assertEquals('slices.slice_a', SliceAStub::make([])->viewName());
    }

    public function test_using_changes_view_name()
    {
        $this->assertEquals('slice_a_alt', SliceAStub::make([])->using('slice_a_alt')->viewName());
    }

    public function test_data()
    {
        $this->assertEquals('bar', SliceAStub::make(['foo' => 'bar'])->getFoo());
    }
}
