<?php

namespace Soyhuce\DatabaseObject;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonSerializable;
use Soyhuce\DatabaseObject\Concerns\CastsAttributes;
use Soyhuce\DatabaseObject\Concerns\HasCollection;
use Soyhuce\DatabaseObject\Concerns\HasFactory;
use Soyhuce\DatabaseObject\Concerns\SerializesToArray;
use Soyhuce\DatabaseObject\Factory\DatabaseObjectFactory;

abstract class DatabaseObject implements JsonSerializable, Arrayable
{
    use HasFactory;
    use HasCollection;
    use CastsAttributes;
    use SerializesToArray;

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
}
