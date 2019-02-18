<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Collection;

class Traverser
{
    protected $query;
    protected $document;
    protected $parentId;
    protected $childrenIds;
    protected $parentMethods;
    protected $childrenMethods;

    public function __construct($document)
    {
        $this->document = $document;

        $this->query = Query::eagerLoadAll();

        $this->parentMethods = collect(
            array_fill_keys(Prismic::$documents, 'parent')
        );

        $this->childrenMethods = collect(
            array_fill_keys(Prismic::$documents, 'children')
        );
    }

    public function setParentId($id): Traverser
    {
        $this->parentId = $id;

        return $this;
    }

    public function setChildrenIds($ids): Traverser
    {
        $this->childrenIds = collect($ids);

        return $this;
    }

    public function setParentMethods($methods): Traverser
    {
        $this->parentMethods->merge($methods);

        return $this;
    }

    public function setChildrenMethods(array $methods): Traverser
    {
        $this->childrenMethods->merge($methods);

        return $this;
    }

    public function parent(): ?Document
    {
        if ($this->parentId === false) {
            return null;
        }

        if ($this->parentId) {
            return $this->getParentFromId();
        }

        return $this->getParentFromChildren();
    }

    protected function getParentFromId(): ?Document
    {
        return $this->query->find($this->parentId);
    }

    protected function getParentFromChildren(): ?Document
    {
        return $this->query->recordCache()->first(function ($document) {
            $childrenMethod = $this->getChildrenMethod($document);

            if (method_exists($document, $childrenMethod)) {
                return $document->{$childrenMethod}()
                    ->filter()
                    ->first(function ($document) {
                        return $document->id == $this->document->id;
                    });
            }
        });
    }

    public function children(): Collection
    {
        if ($this->childrenIds === false) {
            return collect();
        }

        if (count($this->childrenIds)) {
            return $this->getChildrenFromIds();
        }

        return $this->getChildrenFromParent();
    }

    protected function getChildrenFromIds(): Collection
    {
        return $this->childrenIds->map(function($id) {
            return $this->query->find($id);
        });
    }

    protected function getChildrenFromParent(): Collection
    {
        // @todo
        return collect();
    }

    public function ancestors(): Collection
    {
        return $this->getAncestors()->map(function($id) {
            return $this->query->find($id);
        });
    }

    public function ancestorsAndSelf(): Collection
    {
        return $this->ancestors()->push($this->document);
    }

    protected function getAncestors(): Collection
    {
        $ancestors = collect();

        $parentMethod = $this->getParentMethod($this->document);

        if ($parent = $this->document->{$parentMethod}()) {
            $ancestors->prepend($parent->id);

            $ancestors = $parent->traverse()->getAncestors()->merge($ancestors);
        }

        return $ancestors;
    }

    public function descendants(): Collection
    {
        return $this->getDescendants()->map(function($id) {
            return $this->query->find($id);
        });
    }

    public function descendantsAndSelf(): Collection
    {
        return $this->descendants()->prepend($this->document);
    }

    protected function getDescendants(): Collection
    {
        $descendants = collect();

        $childrenMethod = $this->getChildrenMethod($this->document);

        if ($children = $this->document->{$childrenMethod}()) {
            foreach ($children as $child) {
                $descendants = $descendants
                    ->push($child->id)
                    ->merge($child->traverse()->getDescendants());
            }
        }

        return $descendants;
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

    protected function getParentMethod(Document $document)
    {
        return $this->parentMethods->get(get_class($document));
    }

    protected function getChildrenMethod(Document $document)
    {
        return $this->childrenMethods->get(get_class($document));
    }
}
