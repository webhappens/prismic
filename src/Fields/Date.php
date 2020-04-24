<?php

namespace WebHappens\Prismic\Fields;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use Prismic\Dom\Date as PrismicDate;

class Date implements Htmlable
{
    protected $carbon;
    protected $stringValue;

    public static function make(...$parameters): self
    {
        return new static(...$parameters);
    }

    public function __construct($date)
    {
        if ( ! $date instanceof Carbon) {
            $date = Carbon::instance(PrismicDate::asDate($date));
        }

        $this->setCarbonInstance($date);
    }

    public function setStringValue(string $value)
    {
        $this->stringValue = $value;

        return $this;
    }

    public function toHtml()
    {
        return (resolve(DateHtmlSerializer::class))->serialize($this);
    }

    public function asCarbon()
    {
        return $this->carbon;
    }

    public function __toString()
    {
        return $this->stringValue;
    }

    public function __call($method, $parameters)
    {
        $carbon = $this->carbon->{$method}(...$parameters);

        if ($carbon instanceof Carbon) {
            $this->setCarbonInstance($carbon);

            return $this;
        }

        return static::make($this->carbon)->setStringValue($carbon);
    }

    private function setCarbonInstance(Carbon $carbon)
    {
        $this->carbon = $carbon;
        $this->setStringValue($carbon);
    }
}
