<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Collection;

class Traverser
{
    protected static $documents;

    protected $id;
    protected $parentId;
    protected $childrenIds;
    protected $ancestors;
    protected $descendants;
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
        return $this->parentId ? $this->getParentFromId() : $this->getParentFromChildren();
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
        return count($this->childrenIds) ? $this->getChildrenFromIds() : $this->getChildrenFromParent();
    }

    protected function getChildrenFromIds(): Collection
    {
        return $this->childrenIds->map(function ($id) {
            return static::getDocuments()->get($id);
        });
    }

    protected function getChildrenFromParent(): Collection
    {
        //
        return collect();
    }

    public function ancestors(): Collection
    {
        return $this->getAncestors(
            $this->getDocuments()->get($this->id)
        );
    }

    protected function getAncestors(Document $document): Collection
    {
        if ( ! $this->ancestors instanceof Collection) {
            $this->ancestors = collect();
        }

        if ($document->getType() == 'homepage') {
            dd('homepage');
        }

        $parentMethod = $this->getParentMethod($document);

        if ($parent = $document->{$parentMethod}()) {
            $this->ancestors->prepend($parent);
            $this->getAncestors($parent);
        }

        return $this->ancestors;
    }

    public function descendants(): Collection
    {
        //
    }

    public function siblings(): Collection
    {
        //
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
        if (static::$documents) {
            return static::$documents;
        }

        static::$documents = collect();

        foreach (Prismic::$documents as $document) {
            static::$documents = static::$documents->merge($document::all()->keyBy('id'));
        }

        return static::$documents;
    }
}
