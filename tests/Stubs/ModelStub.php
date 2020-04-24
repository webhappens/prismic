<?php

namespace WebHappens\Prismic\Tests\Stubs;

use ArrayAccess;
use Carbon\Carbon;
use WebHappens\Prismic\HasAttributes;

abstract class BaseModel implements ArrayAccess
{
    use HasAttributes;
}

class ModelStub extends BaseModel
{
    protected $casts = [
        'last_updated' => 'date',
    ];

    public function getNameAttribute()
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst($value);

        return $this;
    }

    public function getLastNameAttribute($value)
    {
        return ucfirst($value);
    }

    protected function customCastAttribute($type, $value)
    {
        switch ($type) {
            case 'date':
                return Carbon::parse($value);
        }
    }
}
