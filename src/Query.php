<?php

namespace WebHappens\Prismic;

use stdClass;
use Prismic\Api;
use Prismic\Predicates;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Collection;

class Query
{
    protected $type;
    protected $wheres = [];
    protected $options = [];
    protected $cacheQueryResults = false;
    protected static $allRecordsCached = false;
    protected static $cachedRecords = [];

    public static function eagerLoadAll(): Query
    {
        if ( ! static::$allRecordsCached) {
            (new static)->cache()->get();
            static::$allRecordsCached = true;
        }

        return new static;
    }

    public static function setRecordCache(Collection $records): Collection
    {
        return static::$cachedRecords = $records->keyBy('id');
    }

    public static function clearRecordCache(): Collection
    {
        return static::$cachedRecords = collect();
    }

    public static function addToRecordCache(Collection $records): Collection
    {
        $records = $records->keyBy('id');

        static::setRecordCache(static::recordCache()->merge($records));

        return $records;
    }

    public static function recordCache()
    {
        return collect(static::$cachedRecords);
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
        return (bool) $this->cacheQueryResults;
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

        if (static::recordCache()->has($id)) {
            return static::recordCache()->get($id);
        }

        return $this->where('id', $id)->first();
    }

    public function findMany(array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        if (($results = static::recordCache()->only($ids)->values()) && count($ids) === $results->count()) {
            return $results;
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
        $record = $this->processResults($this->getRaw()->results)->first();

        return $this->shouldCache() ? static::addToRecordCache(collect([$record])) : $record;
    }

    public function chunk($pageSize, callable $callback)
    {
        if ($pageSize > 100) {
            throw new InvalidArgumentException('The maximum chunk limit allowed by Prismic is 100');
        }

        $page = 1;
        do {
            $response = $this->options(compact('pageSize', 'page'))->getRaw();

            $results = $this->processResults($response->results);

            $callback(
                $this->shouldCache() ? static::addToRecordCache($results) : $results
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

    protected function processResults($results): Collection
    {
        return collect($results)->map(function ($result) {
            return Document::newHydratedInstance($result);
        });
    }
}
