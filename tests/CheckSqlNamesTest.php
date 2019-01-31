<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Select;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @internal
 */
final class CheckSqlNamesTest extends TestCase
{
    public function testIsValidSqlName(): void
    {
        $query = (new Select('demo'));
        $method = new ReflectionMethod($query, 'isValidSqlName');
        $method->setAccessible(true);

        static::assertTrue($method->invoke($query, 'p'));
        static::assertTrue($method->invoke($query, 'p.field'));
        static::assertTrue($method->invoke($query, 'p.table.col'));
        static::assertTrue($method->invoke($query, 'semi_long_table_name'));
        static::assertTrue($method->invoke($query, 'semi_lo43g_table_n22e'));

        static::assertFalse($method->invoke($query, '58semi_lo43g_table_n22e'));
        static::assertFalse($method->invoke($query, '2col'));
        static::assertFalse($method->invoke($query, 'p.'));
        static::assertFalse($method->invoke($query, 'p.table.'));
    }
}
