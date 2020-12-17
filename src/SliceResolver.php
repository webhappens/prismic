<?php

namespace WebHappens\Prismic;

abstract class SliceResolver
{
    protected $document;
    protected $sliceZone;
    protected $type;
    protected $data;

    public function __construct(Document $document, $sliceZone, $type, $data)
    {
        $this->document = $document;
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
        return data_get($this->data, $key, data_get($this->data, "primary.$key", $default));
    }
}
