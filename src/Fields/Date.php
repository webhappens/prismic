<?php

namespace WebHappens\Prismic\Fields;

use Carbon\Carbon;
use Illuminate\Support\HtmlString;
use Prismic\Dom\Date as PrismicDate;
use Illuminate\Contracts\Support\Htmlable;

class Date implements Htmlable
{
    protected $carbon;
    protected $stringValue;

    public static function make(...$parameters): Date
    {
        return new static(...$parameters);
    }

    public function __construct($date)
    {
        if ( ! $date instanceOf Carbon) {
            $date = Carbon::instance(PrismicDate::asDate($date));
        }

        $this->setCarbonInstance($date);
    }

    public function setStringValue(string $value)
    {
        $this->stringValue = $value;

        return $this;
    }

    public function toHtml(): HtmlString
    {
        return new HtmlString(
            (resolve(DateHtmlSerializer::class))->serialize($this)
        );
    }

    public function __toString()
    {
        return $this->stringValue;
    }

    public function __call($method, $parameters)
    {
        $carbon = $this->carbon->{$method}(...$parameters);

        if ($carbon instanceOf Carbon) {
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
