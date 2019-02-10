<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Exceptions\InvalidSqlFieldNameException;
use Girgias\QueryBuilder\Insert;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class BindFieldTest extends TestCase
{
    private const INVALID_NAME = '2col';

    public function testThrowExceptionOnInvalidFieldToBind(): void
    {
        $query = (new Insert('test'));
        static::expectException(InvalidSqlFieldNameException::class);
        $query->bindField(self::INVALID_NAME, 'field');
    }
}
