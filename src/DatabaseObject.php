<?php

namespace Soyhuce\DatabaseObject;

use JsonSerializable;

abstract class DatabaseObject implements JsonSerializable
{
    /**
     * @param array<string, mixed>  $data
     */
    abstract public static function create(array $data): static;

    /**
     * @return array<string, mixed>
     */
    abstract public function jsonSerialize(): array;

    /**
     * @param array<string, mixed>  $data
     */
    public static function fromDatabase(array $data): static
    {
        return static::create($data);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(): array
    {
        return $this->jsonSerialize();
    }
}
