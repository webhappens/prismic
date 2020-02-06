<?php

namespace WebHappens\Prismic;

use Prismic\Predicates;
use WebHappens\Prismic\Query;

trait HasWheres
{
    protected $predicates = [];

    public function where(string $field, $value): Query
    {
        return $this->whereAt($field, $value);
    }

    public function whereAt(string $field, $value): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::at($field, $value));

        return $this;
    }

    public function whereNot(string $field, $value): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::not($field, $value));

        return $this;
    }

    public function whereAny(string $field, $values): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::any($field, $values));

        return $this;
    }

    public function whereIn(string $field, array $values): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::in($field, $values));

        return $this;
    }

    public function whereHas(string $field): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::has($field));

        return $this;
    }

    public function whereMissing(string $field): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::missing($field));

        return $this;
    }

    public function whereFulltext(string $field, string $value): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::fulltext($field, $value));

        return $this;
    }

    public function whereSimilar(string $documentId, int $maxResults): Query
    {
        array_push($this->predicates, Predicates::similar($documentId, $maxResults));

        return $this;
    }

    public function whereLt(string $field, float $lowerBound): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::lt($field, $lowerBound));

        return $this;
    }

    public function whereGt(string $field, float $upperBound): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::gt($field, $upperBound));

        return $this;
    }

    public function whereInRange(string $field, float $lowerBound, float $upperBound): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::inRange($field, $lowerBound, $upperBound));

        return $this;
    }

    public function whereDateBefore(string $field, $before): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::dateBefore($field, $before));

        return $this;
    }

    public function whereDateAfter(string $field, $after): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::dateAfter($field, $after));

        return $this;
    }

    public function whereDateBetween(string $field, $before, $after): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::dateBetween($field, $before, $after));

        return $this;
    }

    public function whereDayOfMonth(string $field, int $day): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::dayOfMonth($field, $day));

        return $this;
    }

    public function whereDayOfMonthBefore(string $field, int $day): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::dayOfMonthBefore($field, $day));

        return $this;
    }

    public function whereDayOfMonthAfter(string $field, int $day): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::dayOfMonthAfter($field, $day));

        return $this;
    }

    public function whereDayOfWeek(string $field, $day): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::dayOfWeek($field, $day));

        return $this;
    }

    public function whereDayOfWeekBefore(string $field, $day): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::dayOfWeekBefore($field, $day));

        return $this;
    }

    public function whereDayOfWeekAfter(string $field, $day): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::dayOfWeekAfter($field, $day));

        return $this;
    }

    public function whereMonth(string $field, $month): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::month($field, $month));

        return $this;
    }

    public function whereMonthBefore(string $field, $month): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::monthBefore($field, $month));

        return $this;
    }

    public function whereMonthAfter(string $field, $month): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::monthAfter($field, $month));

        return $this;
    }

    public function whereYear(string $field, int $year): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::year($field, $year));

        return $this;
    }

    public function whereHour(string $field, int $hour): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::hour($field, $hour));

        return $this;
    }

    public function whereHourBefore(string $field, int $hour): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::hourBefore($field, $hour));

        return $this;
    }

    public function whereHourAfter(string $field, int $hour): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::hourAfter($field, $hour));

        return $this;
    }

    public function whereNear(string $field, float $latitude, float $longitude, float $radius): Query
    {
        $field = $this->resolveFieldName($field);
        array_push($this->predicates, Predicates::near($field, $latitude, $longitude, $radius));

        return $this;
    }
}
