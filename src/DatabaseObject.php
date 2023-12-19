<?php

namespace Soyhuce\DatabaseObject;

use Illuminate\Support\Collection;
use JsonSerializable;
use Soyhuce\DatabaseObject\Factory\DatabaseObjectFactory;

abstract class DatabaseObject implements JsonSerializable
{
    /** @var class-string<\Illuminate\Support\Collection>  */
    protected static string $collectionClass;

    /**
     * @param array<string, mixed>  $data
     */
    abstract public static function fromDatabase(array $data): static;

    /**
     * @return array<string, mixed>
     */
    abstract public function toDatabase(): array;

    /**
     * @param array<string, mixed>  $data
     */
    public static function create(array $data): static
    {
        return static::fromDatabase($data);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toDatabase();
    }

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

    /**
     * @param int|(callable(array<string, mixed>): array<string, mixed>)|array<string, mixed>|null $count
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $state
     * @return \Soyhuce\DatabaseObject\Factory\DatabaseObjectFactory<static, \Illuminate\Support\Collection>
     */
    public static function factory(int|array|callable|null $count =null, array|callable $state = []): DatabaseObjectFactory
    {
        $factory = DatabaseObjectFactory::factoryForDatabaseObject(static::class);

        return $factory
            ->count(is_numeric($count) ? $count : null)
            ->state(is_callable($count) || is_array($count) ? $count : $state);
    }
}
