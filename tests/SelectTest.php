<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\AggregateFunctions;
use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use Girgias\QueryBuilder\Select;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SelectTest extends TestCase
{
    public function testSimpleSelectQuery(): void
    {
        $query = (new Select('posts'))
            ->select('title')
            ->getQuery()
        ;

        static::assertSame('SELECT title FROM posts', $query);
    }

    public function testQuerySelect(): void
    {
        $query = (new Select('posts'))->getQuery();

        static::assertSame('SELECT * FROM posts', $query);
    }

    public function testQueryMultipleSelect(): void
    {
        $query = (new Select('posts'))
            ->select('title', 'category')
            ->getQuery()
        ;

        static::assertSame('SELECT title, category FROM posts', $query);
    }

    public function testQuerySelectAll(): void
    {
        $query = (new Select('posts'))
            ->selectAll()
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM posts', $query);
    }

    public function testQuerySelectAllWithAnAliasSelect(): void
    {
        $query = (new Select('posts'))
            ->selectAs('title', 't')
            ->selectAll()
            ->getQuery()
        ;

        static::assertSame('SELECT *, title AS t FROM posts', $query);
    }

    public function testQuerySelectColumnAlias(): void
    {
        $query = (new Select('posts'))
            ->selectAs('title', 't')
            ->getQuery()
        ;

        static::assertSame('SELECT title AS t FROM posts', $query);
    }

    public function testQueryMultipleDistinct(): void
    {
        $query = (new Select('posts'))
            ->distinct('title', 'category')
            ->getQuery()
        ;

        static::assertSame('SELECT DISTINCT title, category FROM posts', $query);
    }

    public function testQueryDistinctColumnAlias(): void
    {
        $query = (new Select('posts'))
            ->distinctAs('title', 't')
            ->getQuery()
        ;

        static::assertSame('SELECT DISTINCT title AS t FROM posts', $query);
    }

    public function testSelectAggregate(): void
    {
        $query = (new Select('posts'))
            ->selectAggregate('title', AggregateFunctions::COUNT, 'nb_titles')
            ->getQuery()
        ;

        static::assertSame('SELECT COUNT(title) AS nb_titles FROM posts', $query);
    }

    public function testSelectAggregateAndNormalSelect(): void
    {
        $query = (new Select('posts'))
            ->selectAggregate('title', AggregateFunctions::COUNT, 'nb_titles')
            ->select('category')
            ->getQuery()
        ;

        static::assertSame('SELECT COUNT(title) AS nb_titles, category FROM posts', $query);
    }

    public function testSelectAggregateDistinct(): void
    {
        $query = (new Select('posts'))
            ->distinctAggregate('title', AggregateFunctions::COUNT, 'nb_titles')
            ->getQuery()
        ;

        static::assertSame('SELECT COUNT(DISTINCT title) AS nb_titles FROM posts', $query);
    }

    public function testSelectAggregateDistinctAndNormalSelect(): void
    {
        $query = (new Select('posts'))
            ->distinctAggregate('title', AggregateFunctions::COUNT, 'nb_titles')
            ->select('category')
            ->getQuery()
        ;

        static::assertSame('SELECT COUNT(DISTINCT title) AS nb_titles, category FROM posts', $query);
    }

    public function testQueryHavingOr(): void
    {
        $query = (new Select('demo'))
            ->having(
                'score',
                AggregateFunctions::MAX,
                SqlOperators::MORE_THAN_OR_EQUAL,
                500
            )
            ->havingOr(
                'score',
                AggregateFunctions::AVERAGE,
                SqlOperators::MORE_THAN,
                200
            )
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM demo HAVING (MAX(score) >= 500 OR AVG(score) > 200)', $query);
    }

    public function testQueryHavingAndOr(): void
    {
        $query = (new Select('demo'))
            ->having(
                'score',
                AggregateFunctions::MAX,
                SqlOperators::MORE_THAN_OR_EQUAL,
                500
            )
            ->havingOr(
                'score',
                AggregateFunctions::AVERAGE,
                SqlOperators::MORE_THAN,
                200
            )
            ->having(
                'game_time',
                AggregateFunctions::MAX,
                SqlOperators::LESS_THAN,
                180
            )
            ->getQuery()
        ;

        static::assertSame(
            'SELECT * FROM demo HAVING (MAX(score) >= 500 OR AVG(score) > 200) AND MAX(game_time) < 180',
            $query
        );
    }

    public function testQuerySelectWithMostMethods(): void
    {
        $query = (new Select('posts'))
            ->tableAlias('p')
            ->distinct('title')
            ->limit(15, 5)
            ->where('published', SqlOperators::EQUAL, 'status')
            ->having(
                'score',
                AggregateFunctions::AVERAGE,
                SqlOperators::MORE_THAN,
                20
            )
            ->whereLike('author', 'John%', null, 'pattern')
            ->order('date_creation')
            ->group('id')
            ->getQuery()
        ;

        static::assertSame(
            'SELECT DISTINCT title FROM posts AS p WHERE published = :status AND author LIKE :pattern '.
            'GROUP BY id HAVING AVG(score) > 20 ORDER BY date_creation ASC LIMIT 15 OFFSET 5',
            $query
        );
    }

    /** Dangerous queries */
    public function testThrowExceptionOnSelectLimitWithoutOrderClause(): void
    {
        $query = (new Select('test'))->limit(5);
        $this->expectException(DangerousSqlQueryWarning::class);
        $query->getQuery();
    }
}
