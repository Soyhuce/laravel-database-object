<?php

namespace Soyhuce\DatabaseObject\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Soyhuce\DatabaseObject\DatabaseObject;
use Soyhuce\DatabaseObject\Exceptions\CannotCastException;

/**
 * @template TDatabaseObject of \Soyhuce\DatabaseObject\DatabaseObject
 * @implements CastsAttributes<\Illuminate\Support\Collection<array-key,TDatabaseObject>,\Illuminate\Support\Collection<array-key,TDatabaseObject>|array<int, array<array-key, mixed>>>
 */
class DatabaseObjectCollectionCast implements CastsAttributes
{
    /**
     * @param class-string<TDatabaseObject> $class
     * @param class-string<TCollectionClass> $collectionClass
     */
    public function __construct(
        private string $class,
    ) {
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array<string, mixed> $attributes
     * @return \Illuminate\Support\Collection<array-key, TDatabaseObject>|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Collection
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new CannotCastException('DatabaseObjectCollectionCast expects a string');
        }

        $value = Json::decode($value);
        if (!is_array($value)) {
            throw new CannotCastException('Invalid Json string');
        }

        return $this->class::newCollection(
            Arr::map($value, fn ($item) => $this->class::fromDatabase($item))
        );
    }

    /**
     * @param string $key
     * @param \Illuminate\Support\Collection<array-key, TDatabaseObject>|array<int, array<array-key, mixed>>|null $value
     * @param array<string, mixed> $attributes
     * @return array<string, array<array-key, array<string, mixed>>|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        if (is_array($value)) {
            $value = $this->class::newCollection($value);
        }

        if (!$value instanceof Collection) {
            throw new InvalidArgumentException('DatabaseObjectCollectionCast expects a Collection');
        }

        $value = $value->map(fn(mixed $item) => $item instanceof DatabaseObject ? $item : $this->class::create($item));

        return [
            $key => Json::encode(
                $value->map(fn (DatabaseObject $databaseObject) => $databaseObject->toDatabase())
            ),
        ];
    }
}
