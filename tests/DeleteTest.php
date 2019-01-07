<?php

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Delete;
use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use PHPUnit\Framework\TestCase;

class DeleteTest extends TestCase
{
    public function test__Delete__Query()
    {
        $query = (new Delete("posts"))
            ->where("id", SqlOperators::EQUAL, "id")
            ->getQuery();

        $this->assertEquals("DELETE FROM posts WHERE id = :id", $query);
    }

    public function test__Throw__Exception__On__Dangerous__Delete__Query()
    {
        $query = (new Delete('test'));
        $this->expectException(DangerousSqlQueryWarning::class);
        $query->getQuery();
    }
}
