<?php

namespace Soyhuce\DatabaseObject\Concerns;

use Soyhuce\DatabaseObject\Factory\DatabaseObjectFactory;

trait HasFactory
{
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
