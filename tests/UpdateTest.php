<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use Girgias\QueryBuilder\Update;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class UpdateTest extends TestCase
{
    public function testUpdateQueryWithOneParameter(): void
    {
        $query = (new Update('posts'))
            ->where('id', SqlOperators::EQUAL, 1, 'id')
            ->bindField('username', 'Alice', 'username')
            ->getQuery()
        ;

        static::assertSame('UPDATE posts SET username = :username WHERE id = :id', $query);
    }

    public function testUpdateQueryWithTwoParameter(): void
    {
        $query = (new Update('posts'))
            ->where('id', SqlOperators::EQUAL, 1, 'id')
            ->bindField('username', 'Alice', 'username')
            ->bindField('age', 20, 'age')
            ->getQuery()
        ;

        static::assertSame('UPDATE posts SET username = :username, age = :age WHERE id = :id', $query);
    }

    public function testThrowExceptionOnUpdateQueryWithoutParameters(): void
    {
        $query = (new Update('posts'))
            ->where('id', SqlOperators::EQUAL, 1, 'id')
        ;
        $this->expectException(RuntimeException::class);
        $query->getQuery();
    }

    public function testThrowExceptionOnDangerousUpdateQuery(): void
    {
        $query = (new Update('test'));
        $query->bindField('field1', 'field1');
        $this->expectException(DangerousSqlQueryWarning::class);
        $query->getQuery();
    }
}
