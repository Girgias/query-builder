<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Select;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class WhereClauseTest extends TestCase
{
    public function testQueryWhereOr(): void
    {
        $query = (new Select('posts'))
            ->where('author', SqlOperators::EQUAL, 'demo1')
            ->whereOr('author', SqlOperators::EQUAL, 'demo2')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM posts WHERE (author = :demo1 OR author = :demo2)', $query);
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
}
