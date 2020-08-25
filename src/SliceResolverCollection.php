<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Collection;
use WebHappens\Prismic\SliceResolver;

class SliceResolverCollection extends Collection
{
    public function resolve($documentType, $sliceZone, $type, $data = [])
    {
        foreach ($this->items as $resolver) {
            if (is_subclass_of($resolver, SliceResolver::class)) {
                $resolver =  new $resolver($documentType, $sliceZone, $type, $data);

                if ($resolver->shouldResolve()) {
                    return $resolver->resolve();
                }
            }

            if (is_callable($resolver)) {
                return $resolver($data, $type, $sliceZone, $documentType);
            }
        }

        return null;
    }
}
