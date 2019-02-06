<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Insert;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class InsertTest extends TestCase
{
    public function testInsertQueryWithOneParameter(): void
    {
        $query = (new Insert('posts'))
            ->bindField('username', 'Alice', 'username')
            ->getQuery()
        ;

        static::assertSame('INSERT INTO posts (username) VALUES (:username)', $query);
    }

    public function testInsertQueryWithTwoParameter(): void
    {
        $query = (new Insert('posts'))
            ->bindField('username', 'Alice', 'username')
            ->bindField('age', 20, 'age')
            ->getQuery()
        ;

        static::assertSame('INSERT INTO posts (username, age) VALUES (:username, :age)', $query);
    }

    public function testThrowExceptionOnInsertQueryWithoutParameters(): void
    {
        $query = (new Insert('posts'));
        $this->expectException(RuntimeException::class);
        $query->getQuery();
    }
}
