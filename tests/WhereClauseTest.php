<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlOperatorException;
use Girgias\QueryBuilder\Select;
use Girgias\QueryBuilder\Where;
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
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidSqlColumnNameException::class);
        $stub->where(self::INVALID_NAME, SqlOperators::EQUAL, 'random', 'random');
    }

    public function testThrowExceptionOnUndefinedWhereOperator(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(UnexpectedSqlOperatorException::class);
        $stub->where('test', 'not an operator', 'random', 'random');
    }

    public function testThrowExceptionWithHelpMessageWhenNonObviousNotEqualToOperatorUsed(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(UnexpectedSqlOperatorException::class);
        // This tests if the message is contained in the Exception message not an exact comparison.
        static::expectExceptionMessage('Did you mean `<>` (ANSI \'not equal to\' operator) ?');
        $stub->where('test', '!=', 'random', 'random');
    }

    public function testQueryWhereOr(): void
    {
        $query = (new Select('posts'))
            ->where('author', SqlOperators::EQUAL, 'Alice', 'firstAuthor')
            ->whereOr('author', SqlOperators::EQUAL, 'Bob', 'secondAuthor')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM posts WHERE (author = :firstAuthor OR author = :secondAuthor)', $query);
    }

    public function testThrowExceptionWhenWhereOrCalledBeforeAnotherWhereClause(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(RuntimeException::class);
        $stub->whereOr('test', SqlOperators::EQUAL, 'random', 'random');
    }

    public function testThrowExceptionOnInvalidWhereOrColumn(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidSqlColumnNameException::class);
        $stub->whereOr(self::INVALID_NAME, SqlOperators::EQUAL, 'random', 'random');
    }

    public function testThrowExceptionOnUndefinedWhereOrOperator(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(UnexpectedSqlOperatorException::class);
        $stub->whereOr('test', 'not an operator', 'random', 'random');
    }

    public function testQueryWhereAndOr(): void
    {
        $query = (new Select('posts'))
            ->where('author', SqlOperators::EQUAL, 'Alice', 'firstAuthor')
            ->whereOr('author', SqlOperators::EQUAL, 'Bob', 'secondAuthor')
            ->where('published', SqlOperators::EQUAL, true, 'status')
            ->getQuery()
        ;

        static::assertSame(
            'SELECT * FROM posts WHERE (author = :firstAuthor OR author = :secondAuthor) AND published = :status',
            $query
        );
    }

    /**
     * WHERE NULL TESTS.
     */
    public function testWhereIsNull(): void
    {
        $query = (new Select('posts'))
            ->whereIsNull('published')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM posts WHERE published IS NULL', $query);
    }

    public function testWhereIsNotNull(): void
    {
        $query = (new Select('posts'))
            ->whereIsNotNull('published')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM posts WHERE published IS NOT NULL', $query);
    }

    public function testThrowExceptionOnInvalidWhereIsNullColumn(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidSqlColumnNameException::class);
        $stub->whereIsNull(self::INVALID_NAME);
    }

    public function testWhereOrIsNull(): void
    {
        $query = (new Select('demo'))
            ->where('random', SqlOperators::EQUAL, 'Alice', 'random')
            ->whereOrIsNull('random')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM demo WHERE (random = :random OR random IS NULL)', $query);
    }

    public function testWhereOrIsNotNull(): void
    {
        $query = (new Select('demo'))
            ->where('random', SqlOperators::EQUAL, 'Alice', 'random')
            ->whereOrIsNotNull('random')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM demo WHERE (random = :random OR random IS NOT NULL)', $query);
    }

    public function testThrowExceptionWhenWhereOrNullCalledBeforeAnotherWhereClause(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(RuntimeException::class);
        $stub->whereOrIsNull('test');
    }

    /**
     * WHERE LIKE CLAUSE TESTS.
     */
    public function testQueryWhereNotLikeWithEscapeChar(): void
    {
        $query = (new Select('posts'))
            ->whereNotLike('tags', '%UTF#_8', '#', 'pattern')
            ->getQuery()
        ;

        static::assertSame("SELECT * FROM posts WHERE tags NOT LIKE :pattern ESCAPE '#'", $query);
    }

    public function testThrowExceptionOnInvalidWhereLikeColumn(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidSqlColumnNameException::class);
        $stub->whereLike(self::INVALID_NAME, 'a');
    }

    public function testThrowExceptionOnInvalidWhereNotLikeColumn(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidSqlColumnNameException::class);
        $stub->whereNotLike(self::INVALID_NAME, 'a');
    }

    public function testThrowExceptionOnInvalidWhereLikeEscapeChar(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidArgumentException::class);
        $stub->whereNotLike('tags', '%UTF#_8', '##');
    }

    /**
     * WHERE BETWEEN CLAUSE TESTS.
     */
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
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidSqlColumnNameException::class);
        $stub->whereBetween(self::INVALID_NAME, 1, 10);
    }

    public function testThrowExceptionOnInvalidWhereNotBetweenColumn(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidSqlColumnNameException::class);
        $stub->whereNotBetween(self::INVALID_NAME, 1, 10);
    }

    public function testThrowExceptionOnDifferentTypeWhereBetweenValues(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(TypeError::class);
        $stub->whereBetween('demo', 1, (new \DateTime()));
    }

    public function testThrowExceptionOnDifferentTypeWhereNotBetweenValues(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(TypeError::class);
        $stub->whereNotBetween('demo', 1, (new \DateTime()));
    }

    public function testThrowExceptionOnStringValueTypeWhereBetween(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidArgumentException::class);
        $stub->whereBetween('demo', 'a', 'd');
    }

    public function testThrowExceptionOnStringValueTypeWhereNotBetween(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidArgumentException::class);
        $stub->whereNotBetween('demo', 'a', 'd');
    }

    public function testThrowExceptionOnBoolValueTypeWhereBetween(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidArgumentException::class);
        $stub->whereBetween('demo', true, false);
    }

    public function testThrowExceptionOnBoolValueTypeWhereNotBetween(): void
    {
        $stub = static::getMockForAbstractClass(Where::class, [], '', false);
        static::expectException(InvalidArgumentException::class);
        $stub->whereNotBetween('demo', true, false);
    }
}
