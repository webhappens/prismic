<?php

namespace WebHappens\Prismic;

class SliceResolver
{
    protected $resolvers = [];

    public function prepend(callable $resolver)
    {
        array_unshift($this->resolvers, $resolver);

        return $this;
    }

    public function push(callable $resolver)
    {
        array_push($this->resolvers, $resolver);

        return $this;
    }

    public function resolve($type, $data = [])
    {
        if (func_num_args() === 1) {
            $data = (array) $type;
            $type = data_get($data, 'slice_type');
        }

        foreach($this->resolvers as $resolver) {
            if ($result = $resolver($type, $data)) {
                return $result;
            }
        }

        return $this->resolveFromSlices($type, $data);
    }

    public function resolveFromSlices($type, $data) {
        foreach (Prismic::$slices as $slice) {
            if ($slice::getType() != $type) {
                continue;
            }

            if ($slice = $slice::make($data)) {
                return $slice;
            }
        }

        return null;
    }
}
