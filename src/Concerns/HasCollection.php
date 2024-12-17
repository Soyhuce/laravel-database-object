<?php declare(strict_types=1);

namespace Soyhuce\DatabaseObject\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait HasCollection
{
    /** @var class-string<Collection> */
    protected static string $collectionClass;

    /**
     * @template TKey of array-key
     * @param array<TKey, array<string, mixed>> $items
     * @return Collection<TKey, static>
     */
    public static function collection(array $items): Collection
    {
        return static::collectionClass()::make(Arr::map(
            $items,
            fn (array $item) => static::create($item),
        ));
    }

    /**
     * @return class-string<Collection>
     */
    public static function collectionClass(): string
    {
        return static::$collectionClass ?? Collection::class;
    }
}
