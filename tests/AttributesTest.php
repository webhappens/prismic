<?php

namespace WebHappens\Prismic\Tests;

use Carbon\Carbon;
use WebHappens\Prismic\Tests\Stubs\Model;

class AttributesTest extends TestCase
{
    public function testAttributeManipulation()
    {
        $model = new Model;
        $model->foo_bar = 'foobar';
        $this->assertEquals('foobar', $model->foo_bar);
        $this->assertEquals('foobar', $model->fooBar);

        $model = new Model;
        $model->fooBar = 'foobar';
        $this->assertEquals('foobar', $model->foo_bar);
        $this->assertEquals('foobar', $model->fooBar);
    }

    public function testIssetAndUnset()
    {
        $model = new Model;
        $model->foo_bar = 'foobar';
        $this->assertTrue(isset($model->foo_bar));
        $this->assertTrue(isset($model->fooBar));
        unset($model->foo_bar);
        $this->assertFalse(isset($model->foo_bar));
        $this->assertFalse(isset($model->fooBar));

        $model = new Model;
        $model->fooBar = 'foobar';
        $this->assertTrue(isset($model->foo_bar));
        $this->assertTrue(isset($model->fooBar));
        unset($model->fooBar);
        $this->assertFalse(isset($model->foo_bar));
        $this->assertFalse(isset($model->fooBar));
    }

    public function testAccessorsAndMutators()
    {
        $model = new Model;
        $model->first_name = 'ben';
        $model->last_name = 'gurney';
        $this->assertEquals('Ben Gurney', $model->name);
    }

    public function testArrayAccess()
    {
        $model = new Model;
        $model->foo_bar = 'foobar';
        $this->assertEquals('foobar', $model['foo_bar']);
        $this->assertEquals('foobar', $model['fooBar']);

        $model = new Model;
        $model->fooBar = 'foobar';
        $this->assertEquals('foobar', $model['foo_bar']);
        $this->assertEquals('foobar', $model['fooBar']);
    }

    public function testAttributeCast()
    {
        $model = new Model;
        $model->last_updated = '2019-01-01';
        $this->assertInstanceOf(Carbon::class, $model->last_updated);
        $this->assertInstanceOf(Carbon::class, $model->lastUpdated);
    }
}
