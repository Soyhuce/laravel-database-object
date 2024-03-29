<?php declare(strict_types=1);

namespace Soyhuce\DatabaseObject\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Soyhuce\DatabaseObject\DatabaseObject;
use Soyhuce\DatabaseObject\Exceptions\CannotCastException;
use function is_array;
use function is_string;

/**
 * @template TDatabaseObject of \Soyhuce\DatabaseObject\DatabaseObject
 * @implements CastsAttributes<TDatabaseObject,TDatabaseObject|array<string, mixed>>
 */
class DatabaseObjectCast implements Cast, CastsAttributes
{
    /**
     * @param class-string<TDatabaseObject> $class
     */
    public function __construct(
        private string $class,
    ) {}

    /**
     * @param array<string, mixed> $attributes
     * @return TDatabaseObject|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?DatabaseObject
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new CannotCastException('DatabaseObjectCast expects a string');
        }

        $value = Json::decode($value);
        if (!is_array($value)) {
            throw new CannotCastException('Invalid Json string');
        }

        return $this->class::fromDatabase($value);
    }

    /**
     * @param array<string, mixed>|TDatabaseObject|null $value
     * @param array<string, mixed> $attributes
     * @return array<string, array<string, mixed>|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        if (is_array($value)) {
            $value = $this->class::create($value);
        }

        return [$key => Json::encode($value->toDatabase())];
    }
}
