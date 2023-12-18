<?php

namespace Soyhuce\DatabaseObject;

use JsonSerializable;
use Soyhuce\DatabaseObject\Factory\DatabaseObjectFactory;

abstract class DatabaseObject implements JsonSerializable
{
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

    public static function factory(): DatabaseObjectFactory
    {
        return DatabaseObjectFactory::factoryForDatabaseObject(static::class);
    }
}
