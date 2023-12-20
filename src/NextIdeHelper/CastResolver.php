<?php declare(strict_types=1);

namespace Soyhuce\DatabaseObject\NextIdeHelper;

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
            if (Str::startsWith($cast, DatabaseObjectCast::class . ':')) {
                $this->castAsDatabaseObject($model, $name, $cast);
            } elseif (Str::startsWith($cast, DatabaseObjectCollectionCast::class . ':')) {
                $this->castAsDatabaseObjectCollection($model, $name, $cast);
            }
        }
    }

    private function castAsDatabaseObject(Model $model, string $name, string $cast): void
    {
        [, $databaseObjectClass] = explode(':', $cast, 2);

        $attribute = $model->attributes->findByName($name);
        if ($attribute === null) {
            return;
        }

        $attribute->setType("\\{$databaseObjectClass}");
        $attribute->nullable = $attribute->nullableInDatabase;
    }

    private function castAsDatabaseObjectCollection(Model $model, string $name, string $cast): void
    {
        [, $databaseObjectClass] = explode(':', $cast, 2);

        $attribute = $model->attributes->findByName($name);
        if ($attribute === null) {
            return;
        }

        $collectionClass = $databaseObjectClass::newCollection()::class;

        $attribute->setType("\\{$collectionClass}<int, \\{$databaseObjectClass}>");
        $attribute->nullable = $attribute->nullableInDatabase;
    }
}
