<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Delete;
use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DeleteTest extends TestCase
{
    public function testDeleteQuery(): void
    {
        $query = (new Delete('posts'))
            ->where('id', SqlOperators::EQUAL, 1, 'id')
            ->getQuery()
        ;

        static::assertSame('DELETE FROM posts WHERE id = :id', $query);
    }

    public function testThrowExceptionOnDangerousDeleteQuery(): void
    {
        $query = (new Delete('test'));
        $this->expectException(DangerousSqlQueryWarning::class);
        $query->getQuery();
    }
}
