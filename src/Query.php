<?php

namespace WebHappens\Prismic;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Prismic\Api;
use stdClass;

class Query
{
    use HasWheres,
        HasOrderings,
        HasCaching;

    protected $type;
    protected $options = [];

    public static function make(): self
    {
        return new static;
    }

    public function type($type): self
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
                ->map(function ($id) {
                    return static::documentCache()->get($id);
                });
        }

        return $this->whereIn('id', $ids)->get();
    }

    public function single($type = null): ?Document
    {
        $type = $type ?: $this->type;

        if ( ! $type) {
            return null;
        }

        $document = Prismic::documentResolver($this->api()->getSingle($type));

        if ($this->shouldCache) {
            static::addToDocumentCache(collect([$document]));
        }

        return $document;
    }

    public function get(): Collection
    {
        $results = collect();

        $this->chunk(100, function ($chunk) use ($results) {
            $results->push($chunk);
        });

        return $results->flatten();
    }

    public function first()
    {
        $records = $this->hydrateDocuments(array_shift($this->getRaw()->results));

        return $this->shouldCache ? static::addToDocumentCache($records)->first() : $records->first();
    }

    public function count()
    {
        $response = $this->options(['pageSize' => 1, 'page' => 1])->getRaw();

        return $response->total_results_size ?? 0;
    }

    public function paginate($pageSize = 25, $page = 1)
    {
        if ($pageSize > 100) {
            throw new InvalidArgumentException('The maximum chunk limit allowed by Prismic is 100');
        }

        $response = $this->options(compact('pageSize', 'page'))->getRaw();
        $results = $this->hydrateDocuments($response->results);

        if ($this->shouldCache) {
            static::addToDocumentCache($results);
        }

        return new LengthAwarePaginator(
            $results,
            $response->total_results_size,
            $pageSize,
            $page,
            ['path'  => request()->url(), 'query' => request()->query()]
        );
    }

    public function simplePaginate($pageSize = 25, $page = 1)
    {
        if ($pageSize > 100) {
            throw new InvalidArgumentException('The maximum chunk limit allowed by Prismic is 100');
        }

        $response = $this->options(compact('pageSize', 'page'))->getRaw();
        $results = $this->hydrateDocuments($response->results);

        if ($this->shouldCache) {
            static::addToDocumentCache($results);
        }

        return new Paginator(
            $results,
            $pageSize,
            $page,
            ['path'  => request()->url(), 'query' => request()->query()]
        );
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
                $this->shouldCache ? static::addToDocumentCache($results) : $results
            );
        } while ($page++ < $response->total_pages);
    }

    public function toPredicates(): array
    {
        return $this->predicates;
    }

    public function getRaw(): stdClass
    {
        return $this->api()->query($this->toPredicates(), $this->getOptions());
    }

    public function options(array $options = []): self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function getOptions()
    {
        if ($this->orderings) {
            $this->options(['orderings' => '['.implode(',', $this->orderings).']']);
        }

        return $this->options;
    }

    public function api()
    {
        return resolve(Api::class);
    }

    protected function hydrateDocuments($results): Collection
    {
        return collect(Arr::wrap($results))
            ->flatten()
            ->filter()
            ->map(function ($result) {
                return Prismic::documentResolver($result);
            });
    }

    protected function resolveFieldName($field)
    {
        return (new FieldNameResolver($this->type))->resolve($field);
    }
}
