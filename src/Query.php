<?php

namespace WebHappens\Prismic;

use stdClass;
use Prismic\Api;
use Prismic\Predicates;
use Illuminate\Support\Str;
use InvalidArgumentException;
use WebHappens\Prismic\Document;
use Illuminate\Support\Collection;

class Query
{
    protected $type;
    protected $wheres = [];
    protected $options = [];
    protected $cacheQueryResults = false;
    protected static $allDocumentsCached = false;
    protected static $cachedDocuments = [];

    public static function make(): Query
    {
        return new static;
    }

    public static function eagerLoadAll(): Query
    {
        if ( ! static::$allDocumentsCached) {
            static::make()->cache()->get();
            static::$allDocumentsCached = true;
        }

        return new static;
    }

    public static function setDocumentCache(Collection $records): Collection
    {
        return static::$cachedDocuments = $records->keyBy('id');
    }

    public static function clearDocumentCache(): Collection
    {
        return static::$cachedDocuments = collect();
    }

    public static function addToDocumentCache(Collection $documents): Collection
    {
        static::setDocumentCache(static::documentCache()->merge($documents->keyBy('id')));

        return $documents;
    }

    public static function documentCache()
    {
        return collect(static::$cachedDocuments);
    }

    public function cache(): Query
    {
        $this->cacheQueryResults = true;

        return $this;
    }

    public function dontCache(): Query
    {
        $this->cacheQueryResults = false;

        return $this;
    }

    public function shouldCache(): bool
    {
        return $this->cacheQueryResults;
    }

    public function type($type): Query
    {
        $this->type = $type;
        $this->where('type', $type);

        return $this;
    }

    public function find($id): ?Document
    {
        if (is_null($id)) {
            return null;
        }

        if (static::documentCache()->has($id)) {
            return static::documentCache()->get($id);
        }

        return $this->where('id', $id)->first();
    }

    public function findMany(array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        if (static::documentCache()->has($ids)) {
            return collect($ids)
                ->map(function($id) {
                    return static::documentCache()->get($id);
                });
        }

        return $this->where('id', 'in', $ids)->get();
    }

    public function single(): ?Document
    {
        return $this->first();
    }

    public function where($field, $predicate = null, $value = null): Query
    {
        if (func_num_args() === 2) {
            $value = $predicate;
            $predicate = 'at';
        }

        if (in_array($field, Document::getGlobalFieldKeys())) {
            $field = 'document.' . $field;
        } elseif ($this->type && ! Str::contains($field, '.')) {
            $field = 'my.' . $this->type . '.' . $field;
        } else {
            $field = 'my.' . $field;
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
        $records = $this->hydrateDocuments(array_shift($this->getRaw()->results));

        return $this->shouldCache() ? static::addToDocumentCache($records)->first() : $records->first();
    }

    public function chunk($pageSize, callable $callback)
    {
        if ($pageSize > 100) {
            throw new InvalidArgumentException('The maximum chunk limit allowed by Prismic is 100');
        }

        $page = 1;
        do {
            $response = $this->options(compact('pageSize', 'page'))->getRaw();

            $results = $this->hydrateDocuments($response->results);

            $callback(
                $this->shouldCache() ? static::addToDocumentCache($results) : $results
            );
        } while ($page++ < $response->total_pages);
    }

    public function toPredicates(): array
    {
        return collect($this->wheres)
            ->map(function ($where) {
                extract($where);

                return Predicates::{$predicate}($field, $value);
            })
            ->toArray();
    }

    public function getRaw(): stdClass
    {
        return $this->api()->query($this->toPredicates(), $this->options);
    }

    public function options(array $options = []): Query
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function api()
    {
        return resolve(Api::class);
    }

    protected function hydrateDocuments(...$results): Collection
    {
        return collect($results)
            ->flatten()
            ->filter()
            ->map(function ($result) {
                return Document::newHydratedInstance($result);
            });
    }
}
