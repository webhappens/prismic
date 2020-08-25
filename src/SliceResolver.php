<?php

namespace WebHappens\Prismic;

abstract class SliceResolver
{
    protected $documentType;
    protected $sliceZone;
    protected $type;
    protected $data;

    public function __construct($documentType, $sliceZone, $type, $data)
    {
        $this->documentType = $documentType;
        $this->sliceZone = $sliceZone;
        $this->type = $type;
        $this->data = $data;
    }

    public function shouldResolve(): bool
    {
        return true;
    }

    abstract public function resolve();

    public function data($key, $default = null)
    {
        return data_get($this->data, $key, $default);
    }
}
