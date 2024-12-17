<?php declare(strict_types=1);

namespace Soyhuce\DatabaseObject\Concerns;

use Soyhuce\DatabaseObject\Factory\DatabaseObjectFactory;
use function is_array;
use function is_callable;

trait HasFactory
{
    /**
     * @param array<string, mixed>|(callable(array<string, mixed>): array<string, mixed>)|int|null $count
     * @param array<string, mixed>|(callable(array<string, mixed>): array<string, mixed>) $state
     * @return DatabaseObjectFactory<static, \Illuminate\Support\Collection>
     */
    public static function factory(int|array|callable|null $count = null, array|callable $state = []): DatabaseObjectFactory
    {
        $factory = DatabaseObjectFactory::factoryForDatabaseObject(static::class);

        return $factory
            ->count(is_numeric($count) ? $count : null)
            ->state(is_callable($count) || is_array($count) ? $count : $state);
    }
}
