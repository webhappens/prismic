<?php

namespace WebHappens\Prismic;

use Illuminate\Support\Collection;

trait HasHierarchy
{
    protected static $validParents = [];

    private $_ancestors;

    public function getParent(): ?Document
    {
        foreach (static::$validParents as $validParent) {
            $parent = $validParent::all()->first(function ($document) {
                return $document->getChildren()->first(function ($child) {
                    return $child->id == $this->id;
                });
            });

            if ($parent) {
                return $parent;
            }
        }

        return null;
    }

    public function getAncestors(Document $document = null): Collection
    {
        if ( ! $this->_ancestors instanceof Collection) {
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

    public function getChildren(): Collection
    {
        return collect();
    }
}
