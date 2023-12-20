<?php declare(strict_types=1);

namespace Soyhuce\DatabaseObject\Factory;

use Closure;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Factories\CrossJoinSequence;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Soyhuce\DatabaseObject\DatabaseObject;
use Throwable;
use function count;
use function is_array;
use function is_callable;
use function is_string;

/**
 * @template TDatabaseObject of \Soyhuce\DatabaseObject\DatabaseObject
 * @template TCollection of \Illuminate\Support\Collection
 */
abstract class DatabaseObjectFactory
{
    use Conditionable;
    use Macroable;

    public static string $namespace = 'Database\\Factories\\';

    /** @var ?callable */
    protected static $databaseObjectNameResolver = null;

    /** @var ?callable */
    protected static $factoryNameResolver = null;

    /** @var class-string<TDatabaseObject>|null */
    protected ?string $databaseObject = null;

    /** @var class-string<TCollection> */
    protected string $collection = Collection::class;

    protected ?int $count;

    /** @var \Illuminate\Support\Collection<int, callable(array<string, mixed>): array<string, mixed>> */
    protected Collection $states;

    /** @var \Illuminate\Support\Collection<int, Closure(TDatabaseObject): mixed> */
    protected Collection $afterCreating;

    protected Generator $faker;

    /**
     * @param \Illuminate\Support\Collection<int, callable(array<string, mixed>): array<string, mixed>> $states
     * @param \Illuminate\Support\Collection<int, Closure(TDatabaseObject): mixed> $afterCreating
     */
    public function __construct(
        ?int $count = null,
        Collection $states = new Collection(),
        Collection $afterCreating = new Collection(),
    ) {
        $this->count = $count;
        $this->states = $states;
        $this->afterCreating = $afterCreating;
        $this->faker = $this->withFaker();
    }

    /**
     * @return array<string, mixed>
     */
    abstract public function definition(): array;

    /**
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     */
    public static function new(array|callable $attributes = []): static
    {
        return (new static())->state($attributes)->configure();
    }

    public static function times(int $count): static
    {
        return static::new()->count($count);
    }

    public function configure(): static
    {
        return $this;
    }

    /**
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     * @return array<array-key, mixed>
     */
    public function raw(array|callable $attributes = []): array
    {
        if ($this->count === null) {
            return $this->state($attributes)->getExpandedAttributes();
        }

        return array_map(
            fn () => $this->state($attributes)->getExpandedAttributes(),
            range(1, $this->count)
        );
    }

    /**
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     * @return TDatabaseObject
     */
    public function createOne(array|callable $attributes = []): DatabaseObject
    {
        return $this->count(null)->create($attributes);
    }

    /**
     * @param int|iterable<int, array<string, mixed>>|null $records
     * @return TCollection<int, TDatabaseObject>
     */
    public function createMany(int|iterable|null $records = null): Collection
    {
        if (null === $records) {
            $records = $this->count ?? 1;
        }

        if (is_numeric($records)) {
            $records = array_fill(0, $records, []);
        }

        return $this->newCollection(
            collect($records)->map(fn ($record) => $this->state($record)->create())
        );
    }

    /**
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     * @return TCollection<int, TDatabaseObject>|TDatabaseObject
     */
    public function create(array|callable $attributes = []): Collection|DatabaseObject
    {
        if (!empty($attributes)) {
            return $this->state($attributes)->create();
        }

        $results = $this->make();

        $this->callAfterCreating(
            $results instanceof DatabaseObject ? $this->newCollection([$results]) : $results
        );

        return $results;
    }

    /**
     * @return TCollection<int, TDatabaseObject>|TDatabaseObject
     */
    protected function make(): Collection|DatabaseObject
    {
        if ($this->count === null) {
            return $this->makeInstance();
        }

        if ($this->count < 1) {
            return $this->newCollection();
        }

        return $this->newCollection(
            array_map(
                fn () => $this->makeInstance(),
                range(1, $this->count)
            )
        );
    }

    /**
     * @return TDatabaseObject
     */
    protected function makeInstance(): DatabaseObject
    {
        return $this->newDatabaseObject($this->getExpandedAttributes());
    }

    /**
     * @return array<string, mixed>
     */
    protected function getExpandedAttributes(): array
    {
        return $this->expandAttributes($this->getRawAttributes());
    }

    /**
     * @return array<string, mixed>
     */
    protected function getRawAttributes(): array
    {
        return $this->states->reduce(
            function (array $carry, callable $state): array {
                if ($state instanceof Closure) {
                    $state = $state->bindTo($this);
                }

                return array_merge($carry, $state($carry));
            },
            $this->definition()
        );
    }

    /**
     * @return TCollection<int, TDatabaseObject>
     */
    protected function newCollection(mixed $items = []): Collection
    {
        return new ($this->collection)($items);
    }

    /**
     * @param array<string, mixed> $definition
     * @return array<string, mixed>
     */
    protected function expandAttributes(array $definition): array
    {
        return collect($definition)
            ->map($evaluateRelations = function (mixed $attribute) {
                if ($attribute instanceof self) {
                    $attribute = $attribute->create();
                }

                return $attribute;
            })
            ->map(function (mixed $attribute, string $key) use (&$definition, $evaluateRelations) {
                if (is_callable($attribute) && !is_string($attribute) && !is_array($attribute)) {
                    $attribute = $attribute($definition);
                }

                $attribute = $evaluateRelations($attribute);

                $definition[$key] = $attribute;

                return $attribute;
            })
            ->all();
    }

    /**
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $state
     */
    public function state(array|callable $state): static
    {
        return $this->newInstance([
            'states' => $this->states->concat([
                is_callable($state) ? $state : fn () => $state,
            ]),
        ]);
    }

    public function set(string $key, mixed $value): static
    {
        return $this->state([$key => $value]);
    }

    public function sequence(mixed ...$sequence): static
    {
        return $this->state(new Sequence(...$sequence));
    }

    /**
     * @param array<int, mixed> ...$sequence
     */
    public function forEachSequence(array ...$sequence): static
    {
        return $this->state(new Sequence(...$sequence))->count(count($sequence));
    }

    /**
     * @param array<int, mixed> ...$sequence
     */
    public function crossJoinSequence(array ...$sequence): static
    {
        return $this->state(new CrossJoinSequence(...$sequence));
    }

    /**
     * @param Closure(TDatabaseObject): mixed $callback
     */
    public function afterCreating(Closure $callback): static
    {
        return $this->newInstance(['afterCreating' => $this->afterCreating->concat([$callback])]);
    }

    /**
     * @param TCollection<int, TDatabaseObject> $instances
     */
    protected function callAfterCreating(Collection $instances): void
    {
        $instances->each(function (DatabaseObject $model): void {
            $this->afterCreating->each(function ($callback) use ($model): void {
                $callback($model);
            });
        });
    }

    public function count(?int $count): static
    {
        return $this->newInstance(['count' => $count]);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    protected function newInstance(array $arguments = []): static
    {
        return new static(...array_values(array_merge([
            'count' => $this->count,
            'states' => $this->states,
            'afterCreating' => $this->afterCreating,
        ], $arguments)));
    }

    /**
     * @param array<string, mixed> $attributes
     * @return TDatabaseObject
     */
    public function newDatabaseObject(array $attributes = []): DatabaseObject
    {
        $databaseObject = $this->databaseObjectName();

        return $databaseObject::create($attributes);
    }

    /**
     * @return class-string<TDatabaseObject>
     */
    public function databaseObjectName(): string
    {
        $resolver = static::$databaseObjectNameResolver ?? function (self $factory) {
            $namespacedFactoryBasename = Str::replaceLast(
                'Factory',
                '',
                Str::replaceFirst(static::$namespace, '', $factory::class)
            );

            $factoryBasename = Str::replaceLast('Factory', '', class_basename($factory));

            $appNamespace = static::appNamespace();

            return class_exists($appNamespace . 'DatabaseObjects\\' . $namespacedFactoryBasename)
                ? $appNamespace . 'DatabaseObjects\\' . $namespacedFactoryBasename
                : $appNamespace . $factoryBasename;
        };

        return $this->databaseObject ?? $resolver($this);
    }

    /**
     * @param callable(self<\Soyhuce\DatabaseObject\DatabaseObject, \Illuminate\Support\Collection>): class-string<\Soyhuce\DatabaseObject\DatabaseObject> $callback
     */
    public static function guessDatabaseObjectNamesUsing(callable $callback): void
    {
        static::$databaseObjectNameResolver = $callback;
    }

    public static function useNamespace(string $namespace): void
    {
        static::$namespace = $namespace;
    }

    /**
     * @template TObject of \Soyhuce\DatabaseObject\DatabaseObject
     * @param class-string<TObject> $databaseObjectName
     * @return \Soyhuce\DatabaseObject\Factory\DatabaseObjectFactory<TObject, \Illuminate\Support\Collection>
     */
    public static function factoryForDatabaseObject(string $databaseObjectName): self
    {
        $factory = static::resolveFactoryName($databaseObjectName);

        return $factory::new();
    }

    /**
     * @template TObject of \Soyhuce\DatabaseObject\DatabaseObject
     * @param callable(class-string<TObject>): class-string<\Soyhuce\DatabaseObject\Factory\DatabaseObjectFactory<TObject, \Illuminate\Support\Collection>> $callback
     */
    public static function guessFactoryNamesUsing(callable $callback): void
    {
        static::$factoryNameResolver = $callback;
    }

    protected function withFaker(): Generator
    {
        return Container::getInstance()->make(Generator::class);
    }

    /**
     * @template TObject of \Soyhuce\DatabaseObject\DatabaseObject
     * @param class-string<TObject> $databaseObjectName
     * @return class-string<\Soyhuce\DatabaseObject\Factory\DatabaseObjectFactory<TObject, \Illuminate\Support\Collection>>
     */
    public static function resolveFactoryName(string $databaseObjectName): string
    {
        $resolver = static::$factoryNameResolver ?? function (string $modelName) {
            $appNamespace = static::appNamespace();

            $modelName = Str::startsWith($modelName, $appNamespace . 'DatabaseObjects\\')
                ? Str::after($modelName, $appNamespace . 'DatabaseObjects\\')
                : Str::after($modelName, $appNamespace);

            return static::$namespace . $modelName . 'Factory';
        };

        return $resolver($databaseObjectName);
    }

    protected static function appNamespace(): string
    {
        try {
            return Container::getInstance()
                ->make(Application::class)
                ->getNamespace();
        } catch (Throwable) {
            return 'App\\';
        }
    }
}
