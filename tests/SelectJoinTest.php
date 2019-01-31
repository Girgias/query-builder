<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Exceptions\InvalidSqlAliasNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlTableNameException;
use Girgias\QueryBuilder\SelectJoin;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SelectJoinTest extends TestCase
{
    private const INVALID_NAME = '2col';

    public function testThrowExceptionOnInvalidTableName(): void
    {
        $this->expectException(InvalidSqlTableNameException::class);
        (new SelectJoin('demo', self::INVALID_NAME));
    }

    public function testJoinTableAlias(): void
    {
        $query = (new SelectJoin('demo', 'test'))
            ->joinTableAlias('t')
            ->naturalJoin()
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM demo NATURAL JOIN test AS t', $query);
    }

    public function testThrowExceptionOnInvalidTableAlias(): void
    {
        $query = (new SelectJoin('demo', 'test'));
        $this->expectException(InvalidSqlAliasNameException::class);
        $query->joinTableAlias(self::INVALID_NAME);
    }

    public function testThrowExceptionWithoutJoinType(): void
    {
        $query = (new SelectJoin('demo', 'test'));
        $this->expectException(\DomainException::class);
        $query->getQuery();
    }

    public function testCrossJoin(): void
    {
        $query = (new SelectJoin('demo', 'test'))
            ->crossJoin()
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM demo CROSS JOIN test', $query);
    }

    public function testNaturalJoin(): void
    {
        $query = (new SelectJoin('demo', 'test'))
            ->naturalJoin()
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM demo NATURAL JOIN test', $query);
    }

    public function testFullJoin(): void
    {
        $query = (new SelectJoin('demo', 'test'))
            ->fullJoin('test_id', 'id')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM demo FULL JOIN test ON demo.test_id = test.id', $query);
    }

    public function testInnerJoin(): void
    {
        $query = (new SelectJoin('demo', 'test'))
            ->innerJoin('test_id', 'id')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM demo INNER JOIN test ON demo.test_id = test.id', $query);
    }

    public function testLeftJoin(): void
    {
        $query = (new SelectJoin('demo', 'test'))
            ->leftJoin('test_id', 'id')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM demo LEFT JOIN test ON demo.test_id = test.id', $query);
    }

    public function testRightJoin(): void
    {
        $query = (new SelectJoin('demo', 'test'))
            ->rightJoin('test_id', 'id')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM demo RIGHT JOIN test ON demo.test_id = test.id', $query);
    }

    public function testThrowExceptionOnInvalidJoinBaseColumn(): void
    {
        $query = (new SelectJoin('demo', 'test'));
        static::expectException(InvalidSqlColumnNameException::class);
        $query->fullJoin(self::INVALID_NAME, 'id');
    }

    public function testThrowExceptionOnInvalidSelectColumn(): void
    {
        $query = (new SelectJoin('demo', 'test'));
        static::expectException(InvalidSqlColumnNameException::class);
        $query->fullJoin('test_id', self::INVALID_NAME);
    }
}
