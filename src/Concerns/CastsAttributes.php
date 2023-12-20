<?php declare(strict_types=1);

namespace Soyhuce\DatabaseObject\Concerns;

use BackedEnum;
use Carbon\CarbonImmutable;
use DateTimeInterface;

trait CastsAttributes
{
    /**
     * @template TEnum of \BackedEnum
     * @param class-string<TEnum> $enumClass
     * @return ($value is null ? null : TEnum)
     */
    protected static function asEnum(mixed $value, string $enumClass): ?BackedEnum
    {
        if ($value === null) {
            return null;
        }

        if (is_a($value, $enumClass)) {
            return $value;
        }

        return $enumClass::from($value);
    }

    /**
     * @template TDateTime of \DateTimeInterface
     * @param class-string<TDateTime> $dateTimeClass
     * @return ($value is null ? null : TDateTime)
     */
    protected static function asDateTime(mixed $value, string $format = '!Y-m-d H:i:s', string $dateTimeClass = CarbonImmutable::class): ?DateTimeInterface
    {
        if ($value === null) {
            return null;
        }

        if (is_a($value, $dateTimeClass)) {
            return $value;
        }

        return $dateTimeClass::createFromFormat($format, $value);
    }

    /**
     * @template TDateTime of \DateTimeInterface
     * @param class-string<TDateTime> $dateTimeClass
     * @return ($value is null ? null : TDateTime)
     */
    protected static function asDate(mixed $value, string $dateTimeClass = CarbonImmutable::class): ?DateTimeInterface
    {
        return static::asDateTime($value, '!Y-m-d', $dateTimeClass);
    }
}
