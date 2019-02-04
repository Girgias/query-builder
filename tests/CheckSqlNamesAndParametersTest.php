<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Query;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @internal
 */
final class CheckSqlNamesAndParametersTest extends TestCase
{
    /**
     * @covers \Girgias\QueryBuilder\Query::isValidSqlName
     */
    public function testIsValidSqlName(): void
    {
        $query = static::getMockForAbstractClass(Query::class, [], '', false);
        $method = new ReflectionMethod($query, 'isValidSqlName');
        $method->setAccessible(true);

        static::assertTrue($method->invoke($query, 'p'));
        static::assertTrue($method->invoke($query, 'p.field'));
        static::assertTrue($method->invoke($query, 'co.user'));
        static::assertTrue($method->invoke($query, 'p.table.col'));
        static::assertTrue($method->invoke($query, 'semi_long_table_name'));
        static::assertTrue($method->invoke($query, 'semi_lo43g_table_n22e'));

        static::assertFalse($method->invoke($query, '58semi_lo43g_table_n22e'));
        static::assertFalse($method->invoke($query, '2col'));
        static::assertFalse($method->invoke($query, 'p.'));
        static::assertFalse($method->invoke($query, 'p.table.'));
    }

    /**
     * @covers \Girgias\QueryBuilder\Query::isValidSqlParameter
     */
    public function testIsValidSqlParameter(): void
    {
        $query = static::getMockForAbstractClass(Query::class, [], '', false);
        $method = new ReflectionMethod($query, 'isValidSqlParameter');
        $method->setAccessible(true);

        static::assertTrue($method->invoke($query, 'p'));
        static::assertTrue($method->invoke($query, 'ID'));
        static::assertTrue($method->invoke($query, 'namedParameter'));
        static::assertTrue($method->invoke($query, 'Name'));
        static::assertTrue($method->invoke($query, 'category'));

        static::assertFalse($method->invoke($query, 'p.field'));
        static::assertFalse($method->invoke($query, 'co.user'));
        static::assertFalse($method->invoke($query, 'p.table.col'));
        static::assertFalse($method->invoke($query, 'semi_long_table_name'));
        static::assertFalse($method->invoke($query, 'semi_lo43g_table_n22e'));
        static::assertFalse($method->invoke($query, '58semi_lo43g_table_n22e'));
        static::assertFalse($method->invoke($query, '2col'));
        static::assertFalse($method->invoke($query, 'p.'));
        static::assertFalse($method->invoke($query, 'p.table.'));
    }
}
