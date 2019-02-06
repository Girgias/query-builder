<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlOperatorException;
use Girgias\QueryBuilder\Select;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypeError;

/**
 * @internal
 */
final class WhereClauseTest extends TestCase
{
    private const INVALID_NAME = '2col';

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

    public function testQueryWhereOr(): void
    {
        $query = (new Select('posts'))
            ->where('author', SqlOperators::EQUAL, 'demo1')
            ->whereOr('author', SqlOperators::EQUAL, 'demo2')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM posts WHERE (author = :demo1 OR author = :demo2)', $query);
    }

    public function testThrowExceptionWhenWhereOrCalledBeforeAnotherWhereClause(): void
    {
        $query = (new Select('test'));
        $this->expectException(RuntimeException::class);
        $query->whereOr('test', SqlOperators::EQUAL, 'random');
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

    public function testQueryWhereAndOr(): void
    {
        $query = (new Select('posts'))
            ->where('author', SqlOperators::EQUAL, 'demo1')
            ->whereOr('author', SqlOperators::EQUAL, 'demo2')
            ->where('published', SqlOperators::EQUAL, 'status')
            ->getQuery()
        ;

        static::assertSame(
            'SELECT * FROM posts WHERE (author = :demo1 OR author = :demo2) AND published = :status',
            $query
        );
    }

    public function testQueryWhereNotLikeWithEscapeChar(): void
    {
        $query = (new Select('posts'))
            ->whereNotLike('tags', '%UTF#_8', '#')
            ->getQuery()
        ;

        static::assertSame("SELECT * FROM posts WHERE tags NOT LIKE '%UTF#_8' ESCAPE '#'", $query);
    }

    public function testThrowExceptionOnInvalidWhereLikeColumn(): void
    {
        $query = new Select('posts');
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereLike(self::INVALID_NAME, 'a');
    }

    public function testThrowExceptionOnInvalidWhereNotLikeColumn(): void
    {
        $query = new Select('posts');
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereNotLike(self::INVALID_NAME, 'a');
    }

    public function testThrowExceptionOnInvalidWhereLikeEscapeChar(): void
    {
        $query = new Select('posts');
        $this->expectException(InvalidArgumentException::class);
        $query->whereNotLike('tags', '%UTF#_8', '##');
    }

    public function testQueryWhereBetweenIntegers(): void
    {
        $query = (new Select('posts'))
            ->whereBetween('field', 5, 10)
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM posts WHERE field BETWEEN 5 AND 10', $query);
    }

    public function testQueryWhereBetweenDates(): void
    {
        $startDate = new \DateTime('01/01/2016');
        $endDate = new \DateTime('01/01/2017');
        $query = (new Select('posts'))
            ->whereBetween('field', $startDate, $endDate)
            ->getQuery()
        ;

        static::assertSame(
            "SELECT * FROM posts WHERE field BETWEEN '2016-01-01 00:00:00' AND '2017-01-01 00:00:00'",
            $query
        );
    }

    public function testQueryWhereNotBetweenIntegers(): void
    {
        $query = (new Select('posts'))
            ->whereNotBetween('field', 5, 10)
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM posts WHERE field NOT BETWEEN 5 AND 10', $query);
    }

    public function testQueryWhereNotBetweenDates(): void
    {
        $startDate = new \DateTime('01/01/2016');
        $endDate = new \DateTime('01/01/2017');
        $query = (new Select('posts'))
            ->whereNotBetween('field', $startDate, $endDate)
            ->getQuery()
        ;

        static::assertSame(
            "SELECT * FROM posts WHERE field NOT BETWEEN '2016-01-01 00:00:00' AND '2017-01-01 00:00:00'",
            $query
        );
    }

    public function testThrowExceptionOnInvalidWhereBetweenColumn(): void
    {
        $query = new Select('posts');
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereBetween(self::INVALID_NAME, 1, 10);
    }

    public function testThrowExceptionOnInvalidWhereNotBetweenColumn(): void
    {
        $query = new Select('posts');
        $this->expectException(InvalidSqlColumnNameException::class);
        $query->whereNotBetween(self::INVALID_NAME, 1, 10);
    }

    public function testThrowExceptionOnDifferentTypeWhereBetweenValues(): void
    {
        $query = new Select('posts');
        $this->expectException(TypeError::class);
        $query->whereBetween('demo', 1, (new \DateTime()));
    }

    public function testThrowExceptionOnDifferentTypeWhereNotBetweenValues(): void
    {
        $query = new Select('posts');
        $this->expectException(TypeError::class);
        $query->whereNotBetween('demo', 1, (new \DateTime()));
    }

    public function testThrowExceptionOnStringValueTypeWhereBetween(): void
    {
        $query = new Select('posts');
        $this->expectException(InvalidArgumentException::class);
        $query->whereBetween('demo', 'a', 'd');
    }

    public function testThrowExceptionOnStringValueTypeWhereNotBetween(): void
    {
        $query = new Select('posts');
        $this->expectException(InvalidArgumentException::class);
        $query->whereNotBetween('demo', 'a', 'd');
    }

    public function testThrowExceptionOnBoolValueTypeWhereBetween(): void
    {
        $query = new Select('posts');
        $this->expectException(InvalidArgumentException::class);
        $query->whereBetween('demo', true, false);
    }

    public function testThrowExceptionOnBoolValueTypeWhereNotBetween(): void
    {
        $query = new Select('posts');
        $this->expectException(InvalidArgumentException::class);
        $query->whereNotBetween('demo', true, false);
    }
}
