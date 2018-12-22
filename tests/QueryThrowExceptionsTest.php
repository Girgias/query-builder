<?php

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\AggregateFunctions;
use Girgias\QueryBuilder\Delete;
use Girgias\QueryBuilder\Insert;
use Girgias\QueryBuilder\Query;
use Girgias\QueryBuilder\Select;
use Girgias\QueryBuilder\SqlOperators;
use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use Girgias\QueryBuilder\Exceptions\InvalidSqlAliasNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlFieldNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlTableNameException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlFunctionException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlOperatorException;
use Girgias\QueryBuilder\Update;
use InvalidArgumentException;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypeError;

class QueryThrowExceptionsTest extends TestCase
{
    private const INVALID_NAME = '2col';

    /* EXCEPTION THROWS */
    public function test__Throw__Exception__On__Invalid__Table__Name()
    {
        $this->expectException(InvalidSqlTableNameException::class);
        (new Select(self::INVALID_NAME));
    }

    public function test__Throw__Exception__On__Invalid__Table__Alias()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlAliasNameException::class);
        $query->tableAlias(self::INVALID_NAME);
    }

    /** SELECT fields */
    public function test__Throw__Exception__On__Invalid__Select__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlFieldNameException::class);
        $query->select(self::INVALID_NAME);
    }

    public function test__Throw__Exception__On__Invalid__Select__As__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlFieldNameException::class);
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
        $this->expectException(InvalidSqlFieldNameException::class);
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
        $this->expectException(InvalidSqlFieldNameException::class);
        $query->distinct(self::INVALID_NAME);
    }

    public function test__Throw__Exception__On__Invalid__Distinct__As__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlFieldNameException::class);
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
        $this->expectException(InvalidSqlFieldNameException::class);
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

    /** SELECT CLAUSE */
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

    /** WHERE FUNCTIONS EXCEPTIONS */
    public function test__Throw__Exception__On__Invalid__Where__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->where(self::INVALID_NAME, SqlOperators::EQUAL, 'random');
    }
    public function test__Throw__Exception__On__Undefined__Where__Operator()
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlOperatorException::class);
        $query->where('test', 'not an operator', 'random');
    }
    public function test__Throw__Exception__On__Invalid__Where__Or__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereOr(self::INVALID_NAME, SqlOperators::EQUAL, 'random');
    }

    public function test__Throw__Exception__On__Undefined__Where__Or__Operator()
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlOperatorException::class);
        $query->whereOr('test', 'not an operator', 'random');
    }

    public function test__Throw__Exception__When__Where__Or__Called__Before__Another__Where__Clause()
    {
        $query = (new Select('test'));
        $this->expectException(RuntimeException::class);
        $query->whereOr('test', SqlOperators::EQUAL, 'random');
    }

    public function test__Throw__Exception__On__Invalid__Where__Like__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereLike(self::INVALID_NAME, 'a');
    }

    public function test__Throw__Exception__On__Invalid__Where__Not__Like__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereNotLike(self::INVALID_NAME, 'a');
    }

    public function test__Throw__Exception__On__Invalid__Where__Like__Escape__Char()
    {
        $query = (new Select('posts'));
        $this->expectException(InvalidArgumentException::class);
        $query->whereNotLike('tags', '%UTF#_8', '##');
    }

    public function test__Throw__Exception_On_Invalid__Where__Between__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereBetween(self::INVALID_NAME, 1, 10);
    }

    public function test__Throw__Exception_On_Invalid__Where__Not__Between__Column()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereNotBetween(self::INVALID_NAME, 1, 10);
    }

    public function test__Throw__Exception__On__Different__Type__Where__Between__Values()
    {
        $query = (new Select('test'));
        $this->expectException(TypeError::class);
        $query->whereBetween('demo', 1, (new \DateTime()));
    }

    public function test__Throw__Exception__On__Different__Type__Where__Not__Between__Values()
    {
        $query = (new Select('test'));
        $this->expectException(TypeError::class);
        $query->whereNotBetween('demo', 1, (new \DateTime()));
    }

    public function test__Throw__Exception__On__String__Value__Type__Where__Between()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidArgumentException::class);
        $query->whereBetween('demo', 'a', 'd');
    }
    public function test__Throw__Exception__On__String__Value__Type__Where__Not__Between()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidArgumentException::class);
        $query->whereNotBetween('demo', 'a', 'd');
    }

    public function test__Throw__Exception__On__Bool__Value__Type__Where__Between()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidArgumentException::class);
        $query->whereBetween('demo', true, false);
    }
    public function test__Throw__Exception__On__Bool__Value__Type__Where__Not__Between()
    {
        $query = (new Select('test'));
        $this->expectException(InvalidArgumentException::class);
        $query->whereNotBetween('demo', true, false);
    }

    /** HAVING FUNCTION EXCEPTIONS */
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

    /** Exception on bindField method */
    public function test__Throw__Exception__On__Invalid__Field__To__Bind()
    {
        $query = (new Insert('test'));
        $this->expectException(InvalidSqlFieldNameException::class);
        $query->bindField(self::INVALID_NAME, 'field');
    }

    /** Dangerous queries */
    public function test__Throw__Exception__On__Dangerous__Delete__Query()
    {
        $query = (new Delete('test'));
        $this->expectException(DangerousSqlQueryWarning::class);
        $query->getQuery();
    }

    public function test__Throw__Exception__On__Dangerous__Update__Query()
    {
        $query = (new Update('test'));
        $query->bindField('field1', 'field1');
        $this->expectException(DangerousSqlQueryWarning::class);
        $query->getQuery();
    }

    /** Update Query */
    public function test__Throw__Exception__On__Update__Query__Without__Parameters()
    {
        $query = (new Update('posts'))
            ->where('id', SqlOperators::EQUAL, 'id');
        $this->expectException(RuntimeException::class);
        $query->getQuery();
    }

    /** Insert */
    public function test__Throw__Exception__On__Insert__Query__Without__Parameters()
    {
        $query = (new Insert('posts'));
        $this->expectException(RuntimeException::class);
        $query->getQuery();
    }
}
