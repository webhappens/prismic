<?php

namespace WebHappens\Prismic;

use stdClass;
use Prismic\Api;
use Prismic\Predicates;
use InvalidArgumentException;
use Illuminate\Support\Collection;

class Query
{
    protected $document;
    protected $wheres = [];
    protected $options = [];

    public function setDocument(Document $document)
    {
        $this->document = $document;

        return $this;
    }

    public function find($id): ?Document
    {
        if (is_null($id)) {
            return null;
        }

        return $this->where('id', $id)->first();
    }

    public function findMany(array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        return $this->where('id', 'in', $ids)->get();
    }

    public function single(): ?Document
    {
        return $this->first();
    }

    public function where($field, $predicate = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $predicate;
            $predicate = 'at';
        }

        if (in_array($field, $this->document->getGlobalFieldKeys())) {
            $field = 'document.' . $field;
        } else {
            $field = 'my.' . $this->document->getType() . '.' . $field;
        }

        if ( ! method_exists(Predicates::class, $predicate)) {
            throw new InvalidArgumentException('Illegal predicate and value combination.');
        }

        array_push($this->wheres, compact('field', 'predicate', 'value'));

        return $this;
    }

    public function get(): Collection
    {
        $results = collect();

        $this->chunk(100, function ($chunk) use ($results) {
            $results->push($chunk);
        });

        return $results->flatten();
    }

    public function first(): ?Document
    {
        return $this->get()->first();
    }

    public function chunk($pageSize, callable $callback)
    {
        if ($pageSize > 100) {
            // This limit is set by the Prismic API
            throw new InvalidArgumentException('The maximum chunk limit is 100');
        }

        $response = $this->options(compact('pageSize'))->getRaw();

        $callback(
            collect($response->results)->map(function ($result) {
                return $this->document->newHydratedInstance($result);
            })
        );

        for ($page = 2; $page <= $response->total_pages; $page++) {
            $response = $this->options(compact('pageSize', 'page'))->getRaw();

            $callback(
                collect($response->results)->map(function ($result) {
                    return $this->document->newHydratedInstance($result);
                })
            );
        }
    }

    public function getRaw(): stdClass
    {
        $predicates = collect($this->wheres)
            ->map(function ($where) {
                extract($where);

                return Predicates::{$predicate}($field, $value);
            })
            ->toArray();

        return $this->api()->query($predicates, $this->options);
    }

    public function options(array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function api(): Api
    {
        return resolve(Api::class);
    }
}
