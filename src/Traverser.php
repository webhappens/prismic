<?php

namespace WebHappens\Prismic;

use WebHappens\Prismic\Document;
use Illuminate\Support\Collection;

class Traverser
{
    protected $query;
    protected $document;
    protected $parentResolver = [];
    protected $childrenResolver = [];

    public static function make($target = null)
    {
        if (is_null($target)) {
            $target = collect(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3))
                ->map(function($trace) {
                    return data_get($trace, 'object');
                })
                ->first(function($object) {
                    return $object && $object instanceof Document;
                });
        }

        return new static($target);
    }

    public function __construct(Document $document, $parentResolver = ['default' => 'parent'], $childrenResolver = ['default' => 'children'])
    {
        $this->document = $document;

        $this->query = Query::eagerLoadAll();

        $this->parentResolver = collect($parentResolver);
        $this->childrenResolver = collect($childrenResolver);
    }

    public function resolveParentThrough($attributeOrMethod = 'parent', ...$for): Traverser
    {
        if ( ! $for) {
            $for = ['default'];
        }

        $this->parentResolver = collect($this->parentResolver)->merge(array_fill_keys($for, $attributeOrMethod));

        return $this;
    }

    public function resolveChildrenThrough($attributeOrMethod = 'children', ...$for): Traverser
    {
        if (!$for) {
            $for = ['default'];
        }

        $this->childrenResolver = collect($this->childrenResolver)->merge(array_fill_keys($for, $attributeOrMethod));

        return $this;
    }

    public function parent(): ?Document
    {
        return $this->resolveParent();

    }

    public function parentViaChildren($type = null): ?Document
    {
        return $this->query->documentCache()
            ->reject(function ($document) use ($type) {
                return $type && $document->getType() != $type;
            })
            ->first(function ($document) {
                return $this->resolveChildren($document)
                    ->first(function ($document) {
                        return $document->id === $this->document->id;
                    });
        });
    }

    public function children(): Collection
    {
        return $this->resolveChildren();
    }

    protected function childrenViaParent(): Collection
    {
        // @todo
        return collect();
    }

    public function ancestors(): Collection
    {
        $ancestors = collect();

        if ($parent = $this->resolveParent()) {
            $ancestors->prepend($parent);

            $ancestors = (new static($parent))->ancestors()->merge($ancestors);
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

        $this->resolveChildren()->each(function($child) use ($descendants) {
            $descendants = $descendants
                ->push($child)
                ->merge((new static($child))->descendants());
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
            return $document->id == $this->document->id;
        });
    }

    public function siblingsAndSelf(): Collection
    {
        if ( ! $parent = $this->parent()) {
            return collect();
        }

        return $parent->children();
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

    public function siblingsBefore() : Collection
    {
        return $this->siblingsAndSelf()->slice(0, $this->siblingsPosition());
    }

    public function siblingsPosition()
    {
        return $this->siblingsAndSelf()->search(function ($sibling, $key) {
            return $sibling->id == $this->document->id;
        });
    }

    protected function resolveParent($document = null): ?Document
    {
        return $this->resolveDocument($this->parentResolver, $document ?: $this->document);
    }

    protected function resolveChildren($document = null)
    {
        return $this->resolveDocument($this->childrenResolver, $document ?: $this->document) ?: collect();
    }

    protected function resolveDocument($collection, $document) {
        $attributeOrMethod = $collection->get(get_class($document)) ? : $collection->get('default');

        if (!$attributeOrMethod) {
            return null;
        }

        if (method_exists($document, $attributeOrMethod)) {
            return $document->$attributeOrMethod();
        }

        if (isset($document, $attributeOrMethod)) {
            return $document->$attributeOrMethod;
        }

        return null;
    }
}
