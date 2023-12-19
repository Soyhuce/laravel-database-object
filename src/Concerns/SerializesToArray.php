<?php

namespace Soyhuce\DatabaseObject\Concerns;

trait SerializesToArray
{
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->toDatabase();
    }
}
