<?php

declare(strict_types=1);

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

/**
 * @internal
 */
final class QueryThrowExceptionsTest extends TestCase
{
    private const INVALID_NAME = '2col';

    // EXCEPTION THROWS
    public function testThrowExceptionOnInvalidTableName(): void
    {
        $this->expectException(InvalidSqlTableNameException::class);
        (new Select(self::INVALID_NAME));
    }

    public function testThrowExceptionOnInvalidTableAlias(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlAliasNameException::class);
        $query->tableAlias(self::INVALID_NAME);
    }

    /** Possible exceptions thrown WHERE clause methods */
    public function testThrowExceptionOnInvalidWhereColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->where(self::INVALID_NAME, SqlOperators::EQUAL, 'random');
    }

    public function testThrowExceptionOnUndefinedWhereOperator(): void
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlOperatorException::class);
        $query->where('test', 'not an operator', 'random');
    }

    public function testThrowExceptionWithHelpMessageWhenNonObviousNotEqualToOperatorUsed(): void
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlOperatorException::class);
        // This tests if the message is contained in the Exception message not an exact comparison.
        $this->expectExceptionMessage('Did you mean `<>` (ANSI \'not equal to\' operator) ?');
        $query->where('test', '!=', 'random');
    }

    public function testThrowExceptionOnInvalidWhereOrColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereOr(self::INVALID_NAME, SqlOperators::EQUAL, 'random');
    }

    public function testThrowExceptionOnUndefinedWhereOrOperator(): void
    {
        $query = (new Select('test'));
        $this->expectException(UnexpectedSqlOperatorException::class);
        $query->whereOr('test', 'not an operator', 'random');
    }

    public function testThrowExceptionWhenWhereOrCalledBeforeAnotherWhereClause(): void
    {
        $query = (new Select('test'));
        $this->expectException(RuntimeException::class);
        $query->whereOr('test', SqlOperators::EQUAL, 'random');
    }

    public function testThrowExceptionOnInvalidWhereLikeColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereLike(self::INVALID_NAME, 'a');
    }

    public function testThrowExceptionOnInvalidWhereNotLikeColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereNotLike(self::INVALID_NAME, 'a');
    }

    public function testThrowExceptionOnInvalidWhereLikeEscapeChar(): void
    {
        $query = (new Select('posts'));
        $this->expectException(InvalidArgumentException::class);
        $query->whereNotLike('tags', '%UTF#_8', '##');
    }

    public function testThrowExceptionOnInvalidWhereBetweenColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereBetween(self::INVALID_NAME, 1, 10);
    }

    public function testThrowExceptionOnInvalidWhereNotBetweenColumn(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereNotBetween(self::INVALID_NAME, 1, 10);
    }

    public function testThrowExceptionOnDifferentTypeWhereBetweenValues(): void
    {
        $query = (new Select('test'));
        $this->expectException(TypeError::class);
        $query->whereBetween('demo', 1, (new \DateTime()));
    }

    public function testThrowExceptionOnDifferentTypeWhereNotBetweenValues(): void
    {
        $query = (new Select('test'));
        $this->expectException(TypeError::class);
        $query->whereNotBetween('demo', 1, (new \DateTime()));
    }

    public function testThrowExceptionOnStringValueTypeWhereBetween(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidArgumentException::class);
        $query->whereBetween('demo', 'a', 'd');
    }

    public function testThrowExceptionOnStringValueTypeWhereNotBetween(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidArgumentException::class);
        $query->whereNotBetween('demo', 'a', 'd');
    }

    public function testThrowExceptionOnBoolValueTypeWhereBetween(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidArgumentException::class);
        $query->whereBetween('demo', true, false);
    }

    public function testThrowExceptionOnBoolValueTypeWhereNotBetween(): void
    {
        $query = (new Select('test'));
        $this->expectException(InvalidArgumentException::class);
        $query->whereNotBetween('demo', true, false);
    }

    /** Exception on bindField method */
    public function testThrowExceptionOnInvalidFieldToBind(): void
    {
        $query = (new Insert('test'));
        $this->expectException(InvalidSqlFieldNameException::class);
        $query->bindField(self::INVALID_NAME, 'field');
    }
}
