<?php

namespace WebHappens\Prismic\Contracts;

interface SliceResolver
{
    public function shouldResolve($document, $field, $slice, $data = []);

    public function resolve($type, $data = []);
}
