<?php

namespace Soyhuce\DatabaseObject\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Soyhuce\DatabaseObject\DatabaseObject;
use Soyhuce\DatabaseObject\Exceptions\CannotCastException;

/**
 * @template TDatabaseObject of \Soyhuce\DatabaseObject\DatabaseObject
 * @implements CastsAttributes<TDatabaseObject,TDatabaseObject|array<string, mixed>>
 */
class DatabaseObjectCast implements CastsAttributes
{
    /**
     * @param class-string<TDatabaseObject> $class
     */
    public function __construct(
        private string $class,
    ) {}

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array<string, mixed> $attributes
     * @return TDatabaseObject|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?DatabaseObject
    {
        if($value === null) {
            return null;
        }

        if(!is_string($value)) {
            throw new CannotCastException('DatabaseObjectCast expects a string');
        }

        $value = Json::decode($value);
        if(!is_array($value)) {
            throw new CannotCastException('Invalid Json string');
        }

        return $this->class::fromDatabase($value);
    }

    /**
     * @param string $key
     * @param TDatabaseObject|array<string, mixed>|null $value
     * @param array<string, mixed> $attributes
     * @return array<string, array<string, mixed>|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if($value === null) {
            return [$key => null];
        }

        if (is_array($value)) {
            $value = $this->class::create($value);
        }

        return [$key => Json::encode($value->toDatabase())];
    }
}
