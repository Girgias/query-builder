<?php
/**
 * Source : http://stackoverflow.com/questions/254514/php-and-enumerations
 * Edited and updated by George Peter Banyard <george.banyard@gmail.com>
 */

namespace Girgias\QueryBuilder;

use DomainException;
use ReflectionClass;
use ReflectionException;

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

    /**
     * @return array<string, mixed>
     */
    private static function getConstants(): array
    {
        if (self::$constCacheArray === null) {
            self::$constCacheArray = [];
        }

        $calledClass = get_called_class();
        if (array_key_exists($calledClass, self::$constCacheArray) === false) {
            try {
                $reflect = new ReflectionClass($calledClass);
                self::$constCacheArray[$calledClass] = $reflect->getConstants();
            } catch (ReflectionException $exception) {
                throw new DomainException('Unable to retrieve Enum constants', 0, $exception);
            }
        }

        return self::$constCacheArray[$calledClass];
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
