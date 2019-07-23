<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Collection;

class Traverser
{
    public static $defaultRelations = [
        'id' => 'id',
        'parent' => 'parent',
        'children' => 'children',
    ];

    protected $current;
    protected $relations = [];

    public static function make(...$parameters)
    {
        return new static(...$parameters);
    }

    public function __construct($current, $relations = [])
    {
        $this->current = $current;
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

    public function parent()
    {
        return $this->resolveRelation('parent');
    }

    public function findParent($objects, $class = null)
    {
        return collect($objects)
            ->reject(function ($object) use ($class) {
                return $class && get_class($object) != $class;
            })
            ->first(function ($object) {
                return static::make($object, $this->relations)->children()
                    ->first(function ($object) {
                        return $this->is($object);
                    });
        });
    }

    public function children(): Collection
    {
        return $this->resolveRelation('children', collect())->filter();
    }

    protected function findChildren($objects): Collection
    {
        return collect($objects)
            ->filter(function ($object) {
                return $parent = static::make($object, $this->relations)->parent()
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
        return $this->ancestors()->push($this->current);
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
        return $this->descendants()->prepend($this->current);
    }

    public function siblings(): Collection
    {
        return $this->siblingsAndSelf()->reject(function ($object) {
            return $this->is($object);
        });
    }

    public function siblingsAndSelf(): Collection
    {
        if ( ! $parent = $this->parent()) {
            return collect();
        }

        return static::make($parent, $this->relations)->children();
    }

    public function siblingsNext()
    {
        return $this->siblingsAfter()->first();
    }

    public function siblingsAfter(): Collection
    {
        return $this->siblingsAndSelf()->slice($this->siblingsPosition()+1);

    }

    public function siblingsPrevious()
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

    protected function is($object): bool
    {
        $id = static::make($object, $this->relations)->id();

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

        if (method_exists($this->current, $relation)) {
            return $this->current->$relation();
        }

        if (isset($this->current->$relation)) {
            return $this->current->$relation;
        }

        return $default;
    }

    protected function getRelation($relation)
    {
        $localRelations = collect($this->relations->get(get_class($this->current)));

        return $localRelations->get($relation, static::$defaultRelations[$relation]);
    }
}
