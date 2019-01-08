<?php
declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use Girgias\QueryBuilder\Update;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UpdateTest extends TestCase
{
    public function test__Update__Query__With__One__Parameter()
    {
        $query = (new Update("posts"))
            ->where("id", SqlOperators::EQUAL, "id")
            ->bindField("username", "username")
            ->getQuery();

        $this->assertEquals("UPDATE posts SET username = :username WHERE id = :id", $query);
    }

    public function test__Update__Query__With__Two__Parameter()
    {
        $query = (new Update("posts"))
            ->where("id", SqlOperators::EQUAL, "id")
            ->bindField("username", "username")
            ->bindField("age", "age")
            ->getQuery();

        $this->assertEquals("UPDATE posts SET username = :username, age = :age WHERE id = :id", $query);
    }

    public function test__Throw__Exception__On__Update__Query__Without__Parameters()
    {
        $query = (new Update('posts'))
            ->where('id', SqlOperators::EQUAL, 'id');
        $this->expectException(RuntimeException::class);
        $query->getQuery();
    }

    public function test__Throw__Exception__On__Dangerous__Update__Query()
    {
        $query = (new Update('test'));
        $query->bindField('field1', 'field1');
        $this->expectException(DangerousSqlQueryWarning::class);
        $query->getQuery();
    }
}
