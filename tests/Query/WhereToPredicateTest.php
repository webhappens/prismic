<?php

namespace WebHappens\Prismic\Tests\Query;

use WebHappens\Prismic\Query;
use WebHappens\Prismic\Tests\TestCase;

class WhereToPredicateTest extends TestCase
{
    public function test_where_can_chain()
    {
        $this->assertInstanceOf(Query::class, Query::make()->where('id', '1'));
    }

    public function test_where_with_global_field()
    {
        $predicates = Query::make()
            ->where('id', '1')
            ->toPredicates();

        $this->assertEquals('[:d = at(document.id, "1")]', $predicates[0]->q());
    }

    public function test_where_without_type()
    {
        $predicates = Query::make()->where('example.foo', 'bar')->toPredicates();
        $this->assertEquals('[:d = at(my.example.foo, "bar")]', $predicates[0]->q());
    }

    public function test_where_with_type()
    {
        $predicates = Query::make()
            ->type('example')
            ->where('foo', 'bar')
            ->where('example.baz', 'bar')
            ->toPredicates();

        $this->assertEquals('[:d = at(document.type, "example")]', $predicates[0]->q());
        $this->assertEquals('[:d = at(my.example.foo, "bar")]', $predicates[1]->q());
        $this->assertEquals('[:d = at(my.example.baz, "bar")]', $predicates[2]->q());
    }

    public function test_where_at()
    {
        $predicates = Query::make()->whereAt('example.foo', 'bar')->toPredicates();
        $this->assertEquals('[:d = at(my.example.foo, "bar")]', $predicates[0]->q());
    }

    public function test_where_not()
    {
        $predicates = Query::make()->whereNot('example.foo', 'baz')->toPredicates();
        $this->assertEquals('[:d = not(my.example.foo, "baz")]', $predicates[0]->q());
    }

    public function test_where_any()
    {
        $predicates = Query::make()->whereAny('example.name', ['ben', 'sam'])->toPredicates();
        $this->assertEquals('[:d = any(my.example.name, ["ben", "sam"])]', $predicates[0]->q());
    }

    public function test_where_in()
    {
        $predicates = Query::make()->whereIn('example.name', ['ben', 'sam'])->toPredicates();
        $this->assertEquals('[:d = in(my.example.name, ["ben", "sam"])]', $predicates[0]->q());
    }

    public function test_where_has()
    {
        $predicates = Query::make()->whereHas('example.foo')->toPredicates();
        $this->assertEquals('[:d = has(my.example.foo)]', $predicates[0]->q());
    }

    public function test_where_missing()
    {
        $predicates = Query::make()->whereMissing('example.foo')->toPredicates();
        $this->assertEquals('[:d = missing(my.example.foo)]', $predicates[0]->q());
    }

    public function test_where_fulltext()
    {
        $predicates = Query::make()->whereFulltext('example.foo', 'bar')->toPredicates();
        $this->assertEquals('[:d = fulltext(my.example.foo, "bar")]', $predicates[0]->q());
    }

    public function test_where_similar()
    {
        $predicates = Query::make()->whereSimilar('abc123', 10)->toPredicates();
        $this->assertEquals('[:d = similar("abc123", 10)]', $predicates[0]->q());
    }

    public function test_where_lt()
    {
        $predicates = Query::make()->whereLt('example.foo', 1.5)->toPredicates();
        $this->assertEquals('[:d = number.lt(my.example.foo, 1.5)]', $predicates[0]->q());
    }

    public function test_where_gt()
    {
        $predicates = Query::make()->whereGt('example.foo', 1.5)->toPredicates();
        $this->assertEquals('[:d = number.gt(my.example.foo, 1.5)]', $predicates[0]->q());
    }

    public function test_where_in_range()
    {
        $predicates = Query::make()->whereInRange('example.foo', 1.5, 3.5)->toPredicates();
        $this->assertEquals('[:d = number.inRange(my.example.foo, 1.5, 3.5)]', $predicates[0]->q());
    }

    public function test_where_date_before()
    {
        $predicates = Query::make()->whereDateBefore('example.foo', '2019-04-25')->toPredicates();
        $this->assertEquals('[:d = date.before(my.example.foo, "2019-04-25")]', $predicates[0]->q());
    }

    public function test_where_date_after()
    {
        $predicates = Query::make()->whereDateAfter('example.foo', '2019-04-25')->toPredicates();
        $this->assertEquals('[:d = date.after(my.example.foo, "2019-04-25")]', $predicates[0]->q());
    }

    public function test_where_date_between()
    {
        $predicates = Query::make()->whereDateBetween('example.foo', '2019-04-01', '2019-04-25')->toPredicates();
        $this->assertEquals('[:d = date.between(my.example.foo, "2019-04-01", "2019-04-25")]', $predicates[0]->q());
    }

    public function test_where_day_of_month()
    {
        $predicates = Query::make()->whereDayOfMonth('example.foo', 13)->toPredicates();
        $this->assertEquals('[:d = date.day-of-month(my.example.foo, 13)]', $predicates[0]->q());
    }

    public function test_where_day_of_month_before()
    {
        $predicates = Query::make()->whereDayOfMonthBefore('example.foo', 13)->toPredicates();
        $this->assertEquals('[:d = date.day-of-month-before(my.example.foo, 13)]', $predicates[0]->q());
    }

    public function test_where_day_of_month_after()
    {
        $predicates = Query::make()->whereDayOfMonthAfter('example.foo', 13)->toPredicates();
        $this->assertEquals('[:d = date.day-of-month-after(my.example.foo, 13)]', $predicates[0]->q());
    }

    public function test_where_day_of_week()
    {
        $predicates = Query::make()->whereDayOfWeek('example.foo', 2)->toPredicates();
        $this->assertEquals('[:d = date.day-of-week(my.example.foo, 2)]', $predicates[0]->q());
    }

    public function test_where_day_of_week_before()
    {
        $predicates = Query::make()->whereDayOfWeekBefore('example.foo', 2)->toPredicates();
        $this->assertEquals('[:d = date.day-of-week-before(my.example.foo, 2)]', $predicates[0]->q());
    }

    public function test_where_day_of_week_after()
    {
        $predicates = Query::make()->whereDayOfWeekAfter('example.foo', 2)->toPredicates();
        $this->assertEquals('[:d = date.day-of-week-after(my.example.foo, 2)]', $predicates[0]->q());
    }

    public function test_where_month()
    {
        $predicates = Query::make()->whereMonth('example.foo', 3)->toPredicates();
        $this->assertEquals('[:d = date.month(my.example.foo, 3)]', $predicates[0]->q());
    }

    public function test_where_month_before()
    {
        $predicates = Query::make()->whereMonthBefore('example.foo', 3)->toPredicates();
        $this->assertEquals('[:d = date.month-before(my.example.foo, 3)]', $predicates[0]->q());
    }

    public function test_where_month_after()
    {
        $predicates = Query::make()->whereMonthAfter('example.foo', 3)->toPredicates();
        $this->assertEquals('[:d = date.month-after(my.example.foo, 3)]', $predicates[0]->q());
    }

    public function test_where_year()
    {
        $predicates = Query::make()->whereYear('example.foo', 2005)->toPredicates();
        $this->assertEquals('[:d = date.year(my.example.foo, 2005)]', $predicates[0]->q());
    }

    public function test_where_hour()
    {
        $predicates = Query::make()->whereHour('example.foo', 14)->toPredicates();
        $this->assertEquals('[:d = date.hour(my.example.foo, 14)]', $predicates[0]->q());
    }

    public function test_where_hour_before()
    {
        $predicates = Query::make()->whereHourBefore('example.foo', 14)->toPredicates();
        $this->assertEquals('[:d = date.hour-before(my.example.foo, 14)]', $predicates[0]->q());
    }

    public function test_where_hour_after()
    {
        $predicates = Query::make()->whereHourAfter('example.foo', 14)->toPredicates();
        $this->assertEquals('[:d = date.hour-after(my.example.foo, 14)]', $predicates[0]->q());
    }

    public function test_where_near()
    {
        $predicates = Query::make()->whereNear('example.foo', 48.880401900547, 2.3423677682877, 5)->toPredicates();
        $this->assertEquals('[:d = geopoint.near(my.example.foo, 48.880401900547, 2.3423677682877, 5)]', $predicates[0]->q());
    }
}
