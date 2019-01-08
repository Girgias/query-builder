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

class SelectThrowExceptionsTest extends TestCase
{
    private const INVALID_NAME = '2col';

    /** Possible exceptions thrown SELECT columns */
    public function test__Throw__Exception__On__Invalid__Select__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->select(self::INVALID_NAME);
    }

    public function test__Throw__Exception__On__Invalid__Select__As__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->selectAs(self::INVALID_NAME, 'alias');
    }

    public function test__Throw__Exception__On__Invalid__Select__Alias()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlAliasNameException::class);
        $query->selectAs('demo', self::INVALID_NAME);
    }

    public function test__Throw__Exception__On__Invalid__Column__when__Select__Aggregate()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->selectAggregate(self::INVALID_NAME, AggregateFunctions::COUNT, 'alias');
    }

    public function test__Throw__Exception__On__Invalid__Function__when__Select__Aggregate()
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlFunctionException::class);
        $query->selectAggregate('demo', 'not_a_function', 'alias');
    }

    public function test__Throw__Exception__On__Invalid__Alias__when__Select__Aggregate()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlAliasNameException::class);
        $query->selectAggregate('demo', AggregateFunctions::COUNT, self::INVALID_NAME);
    }

    public function test__Throw__Exception__On__Invalid__Distinct__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->distinct(self::INVALID_NAME);
    }

    public function test__Throw__Exception__On__Invalid__Distinct__As__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->distinctAs(self::INVALID_NAME, 'alias');
    }

    public function test__Throw__Exception__On__Invalid__Distinct__Alias()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlAliasNameException::class);
        $query->distinctAs('demo', self::INVALID_NAME);
    }

    public function test__Throw__Exception__On__Invalid__Column__when__Distinct__Aggregate()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->distinctAggregate(self::INVALID_NAME, AggregateFunctions::COUNT, 'alias');
    }

    public function test__Throw__Exception__On__Invalid__Function__when__Distinct__Aggregate()
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlFunctionException::class);
        $query->distinctAggregate('demo', 'not_a_function', 'alias');
    }

    public function test__Throw__Exception__On__Invalid__Alias__when__Distinct__Aggregate()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlAliasNameException::class);
        $query->distinctAggregate('demo', AggregateFunctions::COUNT, self::INVALID_NAME);
    }

    /** Possible exceptions thrown SELECT clauses */
    public function test__Throw__Exception__On__Invalid__Group__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->group(self::INVALID_NAME);
    }

    public function test__Throw__Exception__On__Invalid__Order__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->order(self::INVALID_NAME);
    }

    public function test__Throw__Exception__On__Invalid__OrderBy__Order()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidArgumentException::class);
        $query->order('test', 'not a valid order');
    }

    public function test__Throw__Exception__On__OutOfRange__Limit()
    {
        $query = (new Select('test'));
        $this->expectException(OutOfRangeException::class);
        $query->limit(-1);
    }

    public function test__Throw__Exception__On__OutOfRange__Offset()
    {
        $query = (new Select('test'));
        $this->expectException(OutOfRangeException::class);
        $query->limit(5, -1);
    }

    /** Possible exceptions thrown HAVING clause methods */
    public function test__Throw__Exception__On__Invalid__Having__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->having(self::INVALID_NAME, AggregateFunctions::MAX, SqlOperators::EQUAL, 10);
    }
    public function test__Throw__Exception__On__Undefined__Having__Operator()
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlOperatorException::class);
        $query->having('test', AggregateFunctions::MAX, 'not an operator', 10);
    }

    public function test__Throw__Exception__On__Undefined__Having__Function()
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlFunctionException::class);
        $query->having('test', 'not a function', SqlOperators::EQUAL, 10);
    }

    public function test__Throw__Exception__On__Invalid__Having__Or__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->havingOr(self::INVALID_NAME, AggregateFunctions::MAX, SqlOperators::EQUAL, 10);
    }

    public function test__Throw__Exception__On__Undefined__Having__Or__Operator()
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlOperatorException::class);
        $query->havingOr('test', AggregateFunctions::MAX, 'not an operator', 10);
    }

    public function test__Throw__Exception__On__Undefined__Having__Or__Function()
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlFunctionException::class);
        $query->havingOr('test', 'not a function', SqlOperators::EQUAL, 10);
    }

    public function test__Throw__Exception__When__Having__Or__Called__Before__Another__Having__Clause()
    {
        $query = (new Select('test'));
        $this->expectException(RuntimeException::class);
        $query->havingOr('test', AggregateFunctions::AVERAGE, SqlOperators::MORE_THAN, 200);
    }
}
