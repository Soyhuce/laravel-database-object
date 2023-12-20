<?php declare(strict_types=1);

namespace Soyhuce\DatabaseObject;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Soyhuce\DatabaseObject\Concerns\CastsAttributes;
use Soyhuce\DatabaseObject\Concerns\HasCollection;
use Soyhuce\DatabaseObject\Concerns\HasFactory;
use Soyhuce\DatabaseObject\Concerns\SerializesToArray;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, mixed>
 */
abstract class DatabaseObject implements Arrayable, JsonSerializable
{
    use CastsAttributes;
    use HasCollection;
    use HasFactory;
    use SerializesToArray;

    /**
     * @param array<string, mixed> $data
     */
    abstract public static function fromDatabase(array $data): static;

    /**
     * @return array<string, mixed>
     */
    abstract public function toDatabase(): array;

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): static
    {
        return static::fromDatabase($data);
    }
}
