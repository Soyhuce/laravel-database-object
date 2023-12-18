<?php

namespace Soyhuce\DatabaseObject;

use JsonSerializable;

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
}
