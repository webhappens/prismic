<?php

namespace WebHappens\Prismic;

use WebHappens\Prismic\Document;
use Illuminate\Support\Collection;

class Traverser
{
    public static $defaultRelations = [
        'id' => 'id',
        'parent' => 'parent',
        'children' => 'children',
    ];

    protected $query;
    protected $document;
    protected $relations = [];

    public static function make(...$parameters)
    {
        return new static(...$parameters);
    }

    public function __construct(Document $document, $relations = [])
    {
        $this->document = $document;

        $this->query = Query::eagerLoadAll();

        $this->relations = collect($relations);
    }

    public function for($class, $parent = null, $children = null, $id = null) {
        $this->relations->put($class, array_filter(compact('parent', 'children', 'id')));

        return $this;
    }

    public function parentFor($class, $parent)
    {
        return $this->for($class, $parent, null, null);
    }

    public function childrenFor($class, $children)
    {
        return $this->for($class, null, $children, null);
    }

    public function idFor($class, $id)
    {
        return $this->for($class, null, null, $id);
    }

    public function id()
    {
        return $this->resolveRelation('id');
    }

    public function parent(): ?Document
    {
        return $this->resolveRelation('parent');
    }

    public function findParent($class = null): ?Document
    {
        return $this->query->documentCache()
            ->reject(function ($document) use ($class) {
                return $class && get_class($document) != $class;
            })
            ->first(function ($document) {
                return static::make($document, $this->relations)->children()
                    ->first(function ($document) {
                        return $this->is($document);
                    });
        });
    }

    public function children(): Collection
    {
        return $this->resolveRelation('children', collect());
    }

    protected function findChildren(): Collection
    {
        return $this->query->documentCache()
            ->filter(function ($document) {
                return $parent = static::make($document, $this->relations)->parent()
                    && $this->is($parent);
            });
    }

    public function ancestors(): Collection
    {
        $ancestors = collect();

        if ($parent = $this->parent()) {
            $ancestors->prepend($parent);

            $ancestors = static::make($parent, $this->relations)->ancestors()->merge($ancestors);
        }

        return $ancestors;
    }

    public function ancestorsAndSelf(): Collection
    {
        return $this->ancestors()->push($this->document);
    }

    public function descendants(): Collection
    {
        $descendants = collect();

        $this->children()->each(function($child) use ($descendants) {
            $descendants = $descendants
                ->push($child)
                ->merge((static::make($child, $this->relations))->descendants());
        });

        return $descendants;
    }

    public function descendantsAndSelf(): Collection
    {
        return $this->descendants()->prepend($this->document);
    }

    public function siblings(): Collection
    {
        return $this->siblingsAndSelf()->reject(function ($document) {
            return $this->is($document);
        });
    }

    public function siblingsAndSelf(): Collection
    {
        if ( ! $parent = $this->parent()) {
            return collect();
        }

        return static::make($parent, $this->relations)->children();
    }

    public function siblingsNext(): ?Document
    {
        return $this->siblingsAfter()->first();
    }

    public function siblingsAfter(): Collection
    {
        return $this->siblingsAndSelf()->slice($this->siblingsPosition()+1);

    }

    public function siblingsPrevious(): ?Document
    {
        return $this->siblingsBefore()->last();

    }

    public function siblingsBefore(): Collection
    {
        return $this->siblingsAndSelf()->slice(0, $this->siblingsPosition());
    }

    public function siblingsPosition()
    {
        return $this->siblingsAndSelf()->search(function ($sibling, $key) {
            return $this->is($sibling);
        });
    }

    protected function is($document): bool
    {
        $id = static::make($document, $this->relations)->id();

        if (is_null($id)) {
            return false;
        }

        return $id === $this->id();
    }

    protected function resolveRelation($relation, $default = null) {
        $relation = $this->getRelation($relation);

        if ( ! $relation) {
            return $default;
        }

        if (method_exists($this->document, $relation)) {
            return $this->document->$relation();
        }

        if (isset($this->document->$relation)) {
            return $this->document->$relation;
        }

        return $default;
    }

    protected function getRelation($relation)
    {
        $localRelations = collect($this->relations->get(get_class($this->document)));

        return $localRelations->get($relation, static::$defaultRelations[$relation]);
    }
}
