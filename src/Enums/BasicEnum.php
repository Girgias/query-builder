<?php
declare(strict_types=1);

/**
 * Source : http://stackoverflow.com/questions/254514/php-and-enumerations
 * Edited and updated by George Peter Banyard <george.banyard@gmail.com>
 */

namespace Girgias\QueryBuilder\Enums;

use ReflectionClass;

/**
 * Class BasicEnum
 * All child classes should be declared as abstract
 */
abstract class BasicEnum
{
    /**
     * @var ?array<string, array<string, mixed>>
     */
    private static $constCacheArray;

    /**
     * BasicEnum constructor to prevent instantiation.
     */
    final private function __construct()
    {
    }

    /**
     * To prevent cloning
     */
    final private function __clone()
    {
    }

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * @return array<string, mixed>
     */
    private static function getConstants(): array
    {
        if (self::$constCacheArray === null) {
            self::$constCacheArray = [];
        }

        if (array_key_exists(static::class, self::$constCacheArray) === false) {
            /** @noinspection PhpUnhandledExceptionInspection*/
            $reflect = new ReflectionClass(static::class);
            self::$constCacheArray[static::class] = $reflect->getConstants();
        }

        return self::$constCacheArray[static::class];
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function isValidName(string $name): bool
    {
        return array_key_exists($name, self::getConstants());
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isValidValue($value): bool
    {
        return in_array($value, array_values(self::getConstants()), true);
    }
}
