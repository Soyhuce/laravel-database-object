<?php

namespace Soyhuce\DatabaseObject;

abstract class DatabaseObject
{
    /**
     * @param array<string, mixed>  $data
     */
    abstract public static function fromDatabase(array $data): static;

    /**
     * @return array<string, mixed>
     */
    abstract public function toDatabase(): array;
}
