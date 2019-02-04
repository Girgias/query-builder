<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Exceptions\DuplicateSqlParameter;
use Girgias\QueryBuilder\Exceptions\InvalidSqlParameterException;
use Girgias\QueryBuilder\Query;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @internal
 */
final class QueryTest extends TestCase
{
    /**
     * @covers \Girgias\QueryBuilder\Query::generateSqlParameter
     */
    public function testGenerateSqlParameter(): void
    {
        $stub = static::getMockForAbstractClass(Query::class, [], '', false);

        $method = new ReflectionMethod($stub, 'generateSqlParameter');
        $method->setAccessible(true);
        $randomString = $method->invoke($stub);
        static::assertSame(1, \preg_match('/^[a-zA-Z]{10}$/', $randomString));
    }

    /**
     * @covers \Girgias\QueryBuilder\Query::addStatementParameter
     */
    public function testAddParameters(): void
    {
        $stub = static::getMockForAbstractClass(Query::class, [], '', false);

        $method = new ReflectionMethod($stub, 'addStatementParameter');
        $method->setAccessible(true);
        $method->invoke($stub, 'named', 'test');

        $property = $method->getDeclaringClass()->getProperty('parameters');
        $property->setAccessible(true);

        static::assertSame(['named' => 'test'], $property->getValue($stub));
    }

    public function testAddParameterWithNullName(): void
    {
        $stub = static::getMockForAbstractClass(Query::class, [], '', false);

        $method = new ReflectionMethod($stub, 'addStatementParameter');
        $method->setAccessible(true);
        $method->invoke($stub, null, 'test');

        $property = $method->getDeclaringClass()->getProperty('parameters');
        $property->setAccessible(true);

        static::assertCount(1, $property->getValue($stub));
    }

    public function testAddParameterWithNullNamesRecursive(): void
    {
        $seed = 45632;
        \mt_srand($seed);

        $stub = static::getMockForAbstractClass(Query::class, [], '', false);

        $method = new ReflectionMethod($stub, 'addStatementParameter');
        $method->setAccessible(true);
        $method->invoke($stub, null, 'test');

        \mt_srand($seed);

        $method->invoke($stub, null, 'recursion');

        $property = $method->getDeclaringClass()->getProperty('parameters');
        $property->setAccessible(true);

        static::assertCount(2, $property->getValue($stub));
    }

    public function testAddParameterExceptionOnInvalidParameterName(): void
    {
        $stub = static::getMockForAbstractClass(Query::class, [], '', false);

        $method = new ReflectionMethod($stub, 'addStatementParameter');
        $method->setAccessible(true);

        $this->expectException(InvalidSqlParameterException::class);
        $method->invoke($stub, '25invalidName', 'test');
    }

    public function testAddParameterExceptionOnDuplicateParameterName(): void
    {
        $stub = static::getMockForAbstractClass(Query::class, [], '', false);

        $method = new ReflectionMethod($stub, 'addStatementParameter');
        $method->setAccessible(true);
        $method->invoke($stub, 'duplicate', 'test');

        $this->expectException(DuplicateSqlParameter::class);
        $method->invoke($stub, 'duplicate', 'demo');
    }

    public function testGetParameters(): void
    {
        $stub = static::getMockForAbstractClass(Query::class, [], '', false);

        static::assertSame([], $stub->getParameters());
    }
}
