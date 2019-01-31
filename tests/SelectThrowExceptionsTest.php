<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\AggregateFunctions;
use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\InvalidSqlAliasNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlFunctionException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlOperatorException;
use Girgias\QueryBuilder\Select;
use InvalidArgumentException;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class SelectThrowExceptionsTest extends TestCase
{
    private const INVALID_NAME = '2col';

    /** Possible exceptions thrown SELECT columns */
    public function testThrowExceptionOnInvalidSelectColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->select(self::INVALID_NAME);
    }

    public function testThrowExceptionOnInvalidSelectAsColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->selectAs(self::INVALID_NAME, 'alias');
    }

    public function testThrowExceptionOnInvalidSelectAlias(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlAliasNameException::class);
        $query->selectAs('demo', self::INVALID_NAME);
    }

    public function testThrowExceptionOnInvalidColumnWhenSelectAggregate(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->selectAggregate(self::INVALID_NAME, AggregateFunctions::COUNT, 'alias');
    }

    public function testThrowExceptionOnInvalidFunctionWhenSelectAggregate(): void
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlFunctionException::class);
        $query->selectAggregate('demo', 'not_a_function', 'alias');
    }

    public function testThrowExceptionOnInvalidAliasWhenSelectAggregate(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlAliasNameException::class);
        $query->selectAggregate('demo', AggregateFunctions::COUNT, self::INVALID_NAME);
    }

    public function testThrowExceptionOnInvalidDistinctColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->distinct(self::INVALID_NAME);
    }

    public function testThrowExceptionOnInvalidDistinctAsColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->distinctAs(self::INVALID_NAME, 'alias');
    }

    public function testThrowExceptionOnInvalidDistinctAlias(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlAliasNameException::class);
        $query->distinctAs('demo', self::INVALID_NAME);
    }

    public function testThrowExceptionOnInvalidColumnWhenDistinctAggregate(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->distinctAggregate(self::INVALID_NAME, AggregateFunctions::COUNT, 'alias');
    }

    public function testThrowExceptionOnInvalidFunctionWhenDistinctAggregate(): void
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlFunctionException::class);
        $query->distinctAggregate('demo', 'not_a_function', 'alias');
    }

    public function testThrowExceptionOnInvalidAliasWhenDistinctAggregate(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlAliasNameException::class);
        $query->distinctAggregate('demo', AggregateFunctions::COUNT, self::INVALID_NAME);
    }

    /** Possible exceptions thrown SELECT clauses */
    public function testThrowExceptionOnInvalidGroupColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->group(self::INVALID_NAME);
    }

    public function testThrowExceptionOnInvalidOrderColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->order(self::INVALID_NAME);
    }

    public function testThrowExceptionOnInvalidOrderByOrder(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidArgumentException::class);
        $query->order('test', 'not a valid order');
    }

    public function testThrowExceptionOnOutOfRangeLimit(): void
    {
        $query = (new Select('test'));
        $this->expectException(OutOfRangeException::class);
        $query->limit(-1);
    }

    public function testThrowExceptionOnOutOfRangeOffset(): void
    {
        $query = (new Select('test'));
        $this->expectException(OutOfRangeException::class);
        $query->limit(5, -1);
    }

    /** Possible exceptions thrown HAVING clause methods */
    public function testThrowExceptionOnInvalidHavingColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->having(self::INVALID_NAME, AggregateFunctions::MAX, SqlOperators::EQUAL, 10);
    }

    public function testThrowExceptionOnUndefinedHavingOperator(): void
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlOperatorException::class);
        $query->having('test', AggregateFunctions::MAX, 'not an operator', 10);
    }

    public function testThrowExceptionOnUndefinedHavingFunction(): void
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlFunctionException::class);
        $query->having('test', 'not a function', SqlOperators::EQUAL, 10);
    }

    public function testThrowExceptionOnInvalidHavingOrColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->havingOr(self::INVALID_NAME, AggregateFunctions::MAX, SqlOperators::EQUAL, 10);
    }

    public function testThrowExceptionOnUndefinedHavingOrOperator(): void
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlOperatorException::class);
        $query->havingOr('test', AggregateFunctions::MAX, 'not an operator', 10);
    }

    public function testThrowExceptionOnUndefinedHavingOrFunction(): void
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlFunctionException::class);
        $query->havingOr('test', 'not a function', SqlOperators::EQUAL, 10);
    }

    public function testThrowExceptionWhenHavingOrCalledBeforeAnotherHavingClause(): void
    {
        $query = (new Select('test'));
        $this->expectException(RuntimeException::class);
        $query->havingOr('test', AggregateFunctions::AVERAGE, SqlOperators::MORE_THAN, 200);
    }
}
