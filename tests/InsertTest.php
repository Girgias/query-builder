<?php
declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Insert;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class InsertTest extends TestCase
{
    public function test__Insert__Query__With__One__Parameter()
    {
        $query = (new Insert("posts"))
            ->bindField("username", "username")
            ->getQuery();

        $this->assertEquals("INSERT INTO posts (username) VALUES (:username)", $query);
    }

    public function test__Insert__Query__With__Two__Parameter()
    {
        $query = (new Insert("posts"))
            ->bindField("username", "username")
            ->bindField("age", "age")
            ->getQuery();

        $this->assertEquals("INSERT INTO posts (username, age) VALUES (:username, :age)", $query);
    }

    public function test__Throw__Exception__On__Insert__Query__Without__Parameters()
    {
        $query = (new Insert('posts'));
        $this->expectException(RuntimeException::class);
        $query->getQuery();
    }
}
