<?php

namespace Soyhuce\DatabaseObject\NextIdeHelper;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Soyhuce\DatabaseObject\Casts\DatabaseObjectCast;
use Soyhuce\DatabaseObject\Casts\DatabaseObjectCollectionCast;
use Soyhuce\NextIdeHelper\Contracts\ModelResolver;
use Soyhuce\NextIdeHelper\Domain\Models\Entities\Model;

class CastResolver implements ModelResolver
{
    public function execute(Model $model): void
    {
        $casts = $model->instance()->getCasts();


        foreach ($casts as $name => $cast) {
            if (Str::startsWith($cast, DatabaseObjectCast::class.':')) {
                $this->castAsDatabaseObject($model, $name, $cast);
            }elseif (Str::startsWith($cast, DatabaseObjectCollectionCast::class.':')) {
                $this->castAsDatabaseObjectCollection($model, $name, $cast);
            }
        }
    }

    private function castAsDatabaseObject(Model $model, string $name, string $cast): void
    {
        [, $arguments] = explode(':', $cast, 2);
        $attribute = $model->attributes->findByName($name);
        if ($attribute === null) {
            return;
        }

        $attribute->setType("\\$arguments");
        $attribute->nullable = false;
    }

    private function castAsDatabaseObjectCollection(Model $model, string $name, string $cast): void
    {
        [, $arguments] = explode(':', $cast, 2);
        [$dataObjectClass, $collectionClass] = explode(',', $arguments, 2) + [null, Collection::class];

        $attribute = $model->attributes->findByName($name);
        if ($attribute === null) {
            return;
        }

        $attribute->setType("\\$collectionClass<int, \\$dataObjectClass>");
        $attribute->nullable = false;
    }
}
