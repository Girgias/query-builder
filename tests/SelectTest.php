<?php

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\AggregateFunctions;
use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use Girgias\QueryBuilder\Select;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    public function test__Simple__Select__Query()
    {
        $query = (new Select("posts"))
            ->select("title")
            ->getQuery();

        $this->assertEquals("SELECT title FROM posts", $query);
    }

    public function test__Query__Select()
    {
        $query = (new Select("posts"))->getQuery();

        $this->assertEquals("SELECT * FROM posts", $query);
    }

    public function test__Query__Multiple__Select()
    {
        $query = (new Select("posts"))
            ->select("title", "category")
            ->getQuery();

        $this->assertEquals("SELECT title, category FROM posts", $query);
    }

    public function test__Query__Select__All()
    {
        $query = (new Select("posts"))
            ->selectAll()
            ->getQuery();

        $this->assertEquals("SELECT * FROM posts", $query);
    }

    public function test__Query__Select__All__With__An__Alias__Select()
    {
        $query = (new Select("posts"))
            ->selectAs("title", "t")
            ->selectAll()
            ->getQuery();

        $this->assertEquals("SELECT *, title AS t FROM posts", $query);
    }

    public function test__Query__Select__Column__Alias()
    {
        $query = (new Select("posts"))
            ->selectAs("title", "t")
            ->getQuery();

        $this->assertEquals("SELECT title AS t FROM posts", $query);
    }

    public function test__Query__Multiple__Distinct()
    {
        $query = (new Select("posts"))
            ->distinct("title", "category")
            ->getQuery();

        $this->assertEquals("SELECT DISTINCT title, category FROM posts", $query);
    }

    public function test__Query__Distinct__Column__Alias()
    {
        $query = (new Select("posts"))
            ->distinctAs("title", "t")
            ->getQuery();

        $this->assertEquals("SELECT DISTINCT title AS t FROM posts", $query);
    }

    public function test__Select__Aggregate()
    {
        $query = (new Select("posts"))
            ->selectAggregate("title", AggregateFunctions::COUNT, "nb_titles")
            ->getQuery();

        $this->assertEquals("SELECT COUNT(title) AS nb_titles FROM posts", $query);
    }

    public function test__Select__Aggregate__and__normal__select()
    {
        $query = (new Select("posts"))
            ->selectAggregate("title", AggregateFunctions::COUNT, "nb_titles")
            ->select("category")
            ->getQuery();

        $this->assertEquals("SELECT COUNT(title) AS nb_titles, category FROM posts", $query);
    }

    public function test__Select__Aggregate__Distinct()
    {
        $query = (new Select("posts"))
            ->distinctAggregate("title", AggregateFunctions::COUNT, "nb_titles")
            ->getQuery();

        $this->assertEquals("SELECT COUNT(DISTINCT title) AS nb_titles FROM posts", $query);
    }

    public function test__Select__Aggregate__Distinct__and__normal__select()
    {
        $query = (new Select("posts"))
            ->distinctAggregate("title", AggregateFunctions::COUNT, "nb_titles")
            ->select("category")
            ->getQuery();

        $this->assertEquals("SELECT COUNT(DISTINCT title) AS nb_titles, category FROM posts", $query);
    }

    public function test__Query__Having__Or()
    {
        $query = (new Select("demo"))
            ->having(
                "score",
                AggregateFunctions::MAX,
                SqlOperators::MORE_THAN_OR_EQUAL,
                500
            )
            ->havingOr(
                "score",
                AggregateFunctions::AVERAGE,
                SqlOperators::MORE_THAN,
                200
            )
            ->getQuery();

        $this->assertEquals("SELECT * FROM demo HAVING (MAX(score) >= 500 OR AVG(score) > 200)", $query);
    }

    public function test__Query__Having__And__Or()
    {
        $query = (new Select("demo"))
            ->having(
                "score",
                AggregateFunctions::MAX,
                SqlOperators::MORE_THAN_OR_EQUAL,
                500
            )
            ->havingOr(
                "score",
                AggregateFunctions::AVERAGE,
                SqlOperators::MORE_THAN,
                200
            )
            ->having(
                "game_time",
                AggregateFunctions::MAX,
                SqlOperators::LESS_THAN,
                180
            )
            ->getQuery();

        $this->assertEquals(
            "SELECT * FROM demo HAVING (MAX(score) >= 500 OR AVG(score) > 200) AND MAX(game_time) < 180",
            $query
        );
    }

    public function test__Query__Select__With__Most__Methods()
    {
        $query = (new Select("posts"))
            ->tableAlias("p")
            ->distinct("title")
            ->limit(15, 5)
            ->where("published", SqlOperators::EQUAL, "status")
            ->having(
                "score",
                AggregateFunctions::AVERAGE,
                SqlOperators::MORE_THAN,
                20
            )
            ->whereLike("author", "John%")
            ->order("date_creation")
            ->group("id")
            ->getQuery();

        $this->assertEquals(
            "SELECT DISTINCT title FROM posts AS p WHERE published = :status AND author LIKE 'John%' " .
            "GROUP BY id HAVING AVG(score) > 20 ORDER BY date_creation ASC LIMIT 15 OFFSET 5",
            $query
        );
    }

    /** Dangerous queries */
    public function test__Throw__Exception__On__Select__Limit__Without__Order__Clause()
    {
        $query = (new Select('test'))->limit(5);
        $this->expectException(DangerousSqlQueryWarning::class);
        $query->getQuery();
    }
}
