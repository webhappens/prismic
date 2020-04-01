<?php

namespace WebHappens\Prismic;

class DocumentResolver
{
    protected static $globalFieldKeys = [
        'id',
        'uid',
        'type',
        'href',
        'tags',
        'first_publication_date',
        'last_publication_date',
        'lang',
        'alternate_languages',
    ];

    protected $resolvers = [];

    public function prepend(callable $resolver)
    {
        array_unshift($this->resolvers,
        $resolver);

        return $this;
    }

    public function push(callable $resolver)
    {
        array_push($this->resolvers, $resolver);

        return $this;
    }

    public function resolve($type, $data = [])
    {
        if (func_num_args() === 1) {
            $data = (array) $type;
            $type = data_get($data, 'type');
        }

        foreach($this->resolvers as $resolver) {
            if ($result = $resolver($type, static::normaliseData($data))) {
                return $result;
            }
        }

        return $this->resolveFromDocuments($type, $data);
    }

    public function resolveFromDocuments($type, $data) {
        foreach (Prismic::$documents as $document) {
            if ($document::getType() != $type) {
                continue;
            }

            if ($document = optional(resolve($document))->hydrate(static::normaliseData($data))) {
                return $document;
            }
        }

        return null;
    }

    public static function getGlobalFieldKeys(): array
    {
        return static::$globalFieldKeys;
    }

    public static function normaliseData($data)
    {
        $returnData = [];

        foreach (static::getGlobalFieldKeys() as $key) {
            $returnData[$key] = data_get($data, $key);
        }

        $data = data_get($data, 'data', []);

        foreach ($data as $key => $value) {
            $returnData[$key] = $value;
        }

        return $returnData;
    }

}
