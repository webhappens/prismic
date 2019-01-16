<?php

namespace WebHappens\Prismic;

use Exception;
use Prismic\Api;
use Carbon\Carbon;
use Prismic\Dom\Date;
use Prismic\Predicates;
use WebHappens\Prismic\DocumentResolver;
use Illuminate\Support\Collection as IlluminateCollection;

abstract class Document
{
    protected static $type;
    protected static $isSingle = false;
    protected static $validParents = [];

    protected $id;
    protected $lastPublished;

    private $_data;
    private $_ancestors;

    public static function getType(): string
    {
        return static::$type;
    }

    public static function isSingle(): bool
    {
        return static::$isSingle;
    }

    public static function single(): Document
    {
        return static::make(
            resolve(Api::class)->getSingle(static::$type)
        );
    }

    public static function find($id): ?Document
    {
        if (is_null($id)) {
            return null;
        }

        $response = resolve(Api::class)->query([
            Predicates::at("document.id", $id),
            Predicates::at("document.type", static::$type),
        ]);

        if ( ! $results = $response->results) {
            return null;
        }

        return static::make($results[0]);
    }

    public static function where($field, $value): IlluminateCollection
    {
        $value = array_wrap($value);

        $response = resolve(Api::class)->query(
            Predicates::any("my." . static::$type . "." . $field, $value)
        );

        return collect($response->results)->map(function ($result) {
            return static::make($result);
        });
    }

    public static function all(): IlluminateCollection
    {
        $results = collect();

        self::chunk(100, function ($chunk) use ($results) {
            $results->push($chunk);
        });

        return $results->flatten();
    }

    public static function chunk($limit, Callable $callback)
    {
        if ($limit > 100) {
            // This limit is set by the Prismic API
            throw new Exception('The maximum chunk limit is 100');
        }

        $response = resolve(Api::class)->query(
            Predicates::at("document.type", static::$type),
            ['pageSize' => $limit]
        );

        $callback(
            collect($response->results)->map(function ($result) {
                return static::find($result->id);
            })
        );

        for ($page = 2; $page <= $response->total_pages; $page++) {
            $response = resolve(Api::class)->query(
                Predicates::at("document.type", static::$type),
                ['pageSize' => $limit, 'page' => $page]
            );

            $callback(
                collect($response->results)->map(function ($result) {
                    return static::find($result->id);
                })
            );
        }
    }

    public static function resolve(...$args) : ?Document
    {
        return resolve(DocumentResolver::class)->resolve(...$args);
    }

    public static function make(...$args): Document
    {
        return new static(...$args);
    }

    public function __construct($data)
    {
        $this->id = data_get($data, 'id');

        $lastPublished = data_get($data, 'last_publication_date');
        $this->lastPublished = $lastPublished ? Carbon::instance(Date::asDate($lastPublished)) : null;

        $this->_data = data_get($data, 'data');

        $this->hydrate();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLastPublished(): ?Carbon
    {
        return $this->lastPublished;
    }

    public function getSlices($types = []): IlluminateCollection
    {
        $types = array_wrap($types);
        $slices = collect($this->data('body') ?? []);

        if (count($types)) {
            $slices = $slices->filter(function ($data) use ($types) {
                return in_array(data_get($data, 'slice_type'), $types);
            });
        }

        return $slices
            ->map(function ($data) {
                if ($slice = Prismic::findSliceByType(data_get($data, 'slice_type'))) {
                    return $slice::make($data);
                }
            })
            ->filter();
    }

    public function getParent(): ?Document
    {
        foreach (static::$validParents as $validParent) {
            $parent = $validParent::all()->first(function ($document) {
                return $document->getChildren()->first(function ($child) {
                    return $child->getId() == $this->getId();
                });
            });

            if ($parent) {
                return $parent;
            }
        }

        return null;
    }

    public function getAncestors(Document $document = null): IlluminateCollection
    {
        if ( ! $this->_ancestors instanceOf IlluminateCollection) {
            $this->_ancestors = collect();
        }

        if (is_null($document)) {
            $document = $this;
        }

        if ($parent = $document->getParent()) {
            $this->_ancestors->prepend($parent);
            $this->getAncestors($parent);
        }

        return $this->_ancestors;
    }

    public function getChildren(): IlluminateCollection
    {
        return collect();
    }

    protected function hydrate()
    {
    }

    protected function data($field, $default = null)
    {
        return data_get($this->_data, $field, $default);
    }
}
