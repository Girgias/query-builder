<?php

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\InvalidSqlAliasNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlFieldNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlTableNameException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlOperatorException;
use Girgias\QueryBuilder\Insert;
use Girgias\QueryBuilder\Select;
use InvalidArgumentException;
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

    /** Possible exceptions thrown WHERE clause methods */
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

    public function test__Throw__Exception__With__Help__Message__When__Non__Obvious__NotEqualTo__Operator__Used()
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlOperatorException::class);
        // This tests if the message is contained in the Exception message not an exact comparison.
        $this->expectExceptionMessage('Did you mean `<>` (ANSI \'not equal to\' operator) ?');
        $query->where('test', '!=', 'random');
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

    /** Exception on bindField method */
    public function test__Throw__Exception__On__Invalid__Field__To__Bind()
    {
        $query = (new Insert('test'));
        $this->expectException(InvalidSqlFieldNameException::class);
        $query->bindField(self::INVALID_NAME, 'field');
    }
}
