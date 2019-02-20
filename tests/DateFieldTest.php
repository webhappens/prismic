<?php

namespace WebHappens\Prismic\Tests;

use Carbon\Carbon;
use WebHappens\Prismic\Fields\Date;

class DateFieldTest extends TestCase
{
    public function test_from_prismic_string_to_string()
    {
        $date = Date::make('2019-02-19T09:13:44+0000');
        $this->assertInstanceOf(Date::class, $date);
        $this->assertEquals('2019-02-19 09:13:44', (string) $date);
    }

    public function test_from_carbon_object_to_string()
    {
        $carbon = Carbon::now();
        $date = Date::make($carbon);
        $this->assertInstanceOf(Date::class, $date);
        $this->assertEquals((string) $carbon, (string) $date);
    }

    public function test_to_html()
    {
        $this->assertEquals(
            '<time datetime="2019-02-19 09:13">2019-02-19 09:13:44</time>',
            Date::make('2019-02-19T09:13:44+0000')->toHtml()
        );
    }

    public function test_can_use_carbon_methods()
    {
        $this->assertEquals(
            '2020-02-19',
            Date::make('2019-02-19T09:13:44+0000')->addYear()->format('Y-m-d')
        );

        $this->assertEquals(
            '2020-02-19 09:13:44',
            (string) Date::make('2019-02-19T09:13:44+0000')->addYear()
        );

        $this->assertEquals(
            '<time datetime="2019-02-19 09:13">2019-02-19 09:13:00</time>',
            Date::make('2019-02-19T09:13:44+0000')->startOfMinute()->toHtml()
        );
    }
}
