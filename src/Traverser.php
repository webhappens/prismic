<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Collection;

class Traverser
{
    protected static $documents;

    protected $id;
    protected $parentId;
    protected $childrenIds;
    protected $parentMethods;
    protected $childrenMethods;

    public function __construct($id)
    {
        $this->id = $id;

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
        return static::getDocuments()->get($this->parentId);
    }

    protected function getParentFromChildren(): ?Document
    {
        return static::getDocuments()->first(function ($document) {
            $childrenMethod = $this->getChildrenMethod($document);

            if (method_exists($document, $childrenMethod)) {
                return $document->{$childrenMethod}()
                    ->filter()
                    ->first(function ($document) {
                        return $document->id == $this->id;
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
        return $this->childrenIds->map(function ($id) {
            return static::getDocuments()->get($id);
        });
    }

    protected function getChildrenFromParent(): Collection
    {
        // @todo
        return collect();
    }

    public function ancestors(): Collection
    {
        return $this->getAncestors(
            $this->getDocuments()->get($this->id)
        );
    }

    public function ancestorsAndSelf(): Collection
    {
        return $this->ancestors()->push(
            $this->getDocuments()->get($this->id)
        );
    }

    protected function getAncestors(Document $document, $ancestors = null): Collection
    {
        if ( ! $ancestors instanceof Collection) {
            $ancestors = collect();
        }

        $parentMethod = $this->getParentMethod($document);

        if ($parent = $document->{$parentMethod}()) {
            $ancestors->prepend($parent);
            $this->getAncestors($parent, $ancestors);
        }

        return $ancestors;
    }

    public function descendants(): Collection
    {
        return $this->getDescendants(
            $this->getDocuments()->get($this->id)
        );
    }

    public function descendantsAndSelf(): Collection
    {
        return $this->descendants()->prepend(
            $this->getDocuments()->get($this->id)
        );
    }

    protected function getDescendants(Document $document, $descendants = null): Collection
    {
        if ( ! $descendants instanceof Collection) {
            $descendants = collect();
        }

        $childrenMethod = $this->getChildrenMethod($document);

        if ($children = $document->{$childrenMethod}()) {
            foreach ($children as $child) {
                $descendants->push($child);
                $this->getDescendants($child, $descendants);
            }
        }

        return $descendants;
    }

    public function siblings(): Collection
    {
        return $this->siblingsAndSelf()->reject(function ($document) {
            return $document->id == $this->id;
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

    protected static function getDocuments(): Collection
    {
        if ( ! static::$documents) {
            static::$documents = (new Query)->get()->keyBy('id');
        }

        return static::$documents;
    }
}
