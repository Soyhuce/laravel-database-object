<?php

namespace Soyhuce\DatabaseObject\Concerns;

use Illuminate\Support\Collection;

trait HasCollection
{
    /** @var class-string<\Illuminate\Support\Collection>  */
    protected static string $collectionClass;

    /**
     * @template TKey of array-key
     * @param array<TKey, array<string, mixed>> $items
     * @return \Illuminate\Support\Collection<int, static>
     */
    public static function collection(array $items): Collection
    {
        return static::newCollection(array_map(
            fn (array $item) => static::create($item),
            $items
        ));
    }

    /**
     * @template TKey of array-key
     * @param array<TKey, static> $items
     * @return \Illuminate\Support\Collection<TKey, static>
     */
    public static function newCollection(array $items = []): Collection
    {
        $collectionClass = static::$collectionClass ?? Collection::class;

        return new $collectionClass($items);
    }
}
