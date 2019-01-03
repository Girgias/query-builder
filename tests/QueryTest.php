<?php

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\AggregateFunctions;
use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Delete;
use Girgias\QueryBuilder\Insert;
use Girgias\QueryBuilder\Select;
use Girgias\QueryBuilder\Update;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function test__Delete__Query()
    {
        $query = (new Delete("posts"))
            ->where("id", SqlOperators::EQUAL, "id")
            ->getQuery();

        $this->assertEquals("DELETE FROM posts WHERE id = :id", $query);
    }

    public function test__Insert__Query__With__One__Parameter()
    {
        $query = (new Insert("posts"))
            ->where("id", SqlOperators::EQUAL, "id")
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

    /** SELECT QUERIES */
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

    public function test__Query__Where__Or()
    {
        $query = (new Select("posts"))
            ->where("author", SqlOperators::EQUAL, "demo1")
            ->whereOr("author", SqlOperators::EQUAL, "demo2")
            ->getQuery();

        $this->assertEquals("SELECT * FROM posts WHERE (author = :demo1 OR author = :demo2)", $query);
    }

    public function test__Query__Where_And__Or()
    {
        $query = (new Select("posts"))
            ->where("author", SqlOperators::EQUAL, "demo1")
            ->whereOr("author", SqlOperators::EQUAL, "demo2")
            ->where("published", SqlOperators::EQUAL, "status")
            ->getQuery();

        $this->assertEquals(
            "SELECT * FROM posts WHERE (author = :demo1 OR author = :demo2) AND published = :status",
            $query
        );
    }

    public function test__Query__Where__Not__Like__With__Escape__Char()
    {
        $query = (new Select("posts"))
            ->whereNotLike("tags", "%UTF#_8", "#")
            ->getQuery();

        $this->assertEquals("SELECT * FROM posts WHERE tags NOT LIKE '%UTF#_8' ESCAPE '#'", $query);
    }

    public function test__Query__Where__Between__Integers()
    {
        $query = (new Select("posts"))
            ->whereBetween("field", 5, 10)
            ->getQuery();

        $this->assertEquals("SELECT * FROM posts WHERE field BETWEEN 5 AND 10", $query);
    }
    public function test__Query__Where__Between__Dates()
    {
        $startDate = new \DateTime("01/01/2016");
        $endDate = new \DateTime("01/01/2017");
        $query = (new Select("posts"))
            ->whereBetween("field", $startDate, $endDate)
            ->getQuery();

        $this->assertEquals(
            "SELECT * FROM posts WHERE field BETWEEN '2016-01-01 00:00:00' AND '2017-01-01 00:00:00'",
            $query
        );
    }

    public function test__Query__Where__Not__Between__Integers()
    {
        $query = (new Select("posts"))
            ->whereNotBetween("field", 5, 10)
            ->getQuery();

        $this->assertEquals("SELECT * FROM posts WHERE field NOT BETWEEN 5 AND 10", $query);
    }

    public function test__Query__Where__Not__Between__Dates()
    {
        $startDate = new \DateTime("01/01/2016");
        $endDate = new \DateTime("01/01/2017");
        $query = (new Select("posts"))
            ->whereNotBetween("field", $startDate, $endDate)
            ->getQuery();

        $this->assertEquals(
            "SELECT * FROM posts WHERE field NOT BETWEEN '2016-01-01 00:00:00' AND '2017-01-01 00:00:00'",
            $query
        );
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

    public function test__Readme__example__1()
    {
        $query = (new \Girgias\QueryBuilder\Select("demo"))
            ->limit(10, 20)
            ->order("published_date")
            ->getQuery();

        $this->assertEquals(
            "SELECT * FROM demo ORDER BY published_date ASC LIMIT 10 OFFSET 20",
            $query
        );
    }

    public function test__Readme__example__2()
    {
        $start = new \DateTime("01/01/2016");
        $end = new \DateTime("01/01/2017");
        $query = (new \Girgias\QueryBuilder\Select("demo"))
            ->select("title", "slug")
            ->selectAs("name_author_post", "author")
            ->whereBetween("date_published", $start, $end)
            ->order("date_published", "DESC")
            ->limit(25)
            ->getQuery();

        $this->assertEquals(
            "SELECT title, slug, name_author_post AS author FROM demo WHERE date_published " .
                "BETWEEN '2016-01-01 00:00:00' AND '2017-01-01 00:00:00' ORDER BY date_published DESC LIMIT 25",
            $query
        );
    }

    public function test__Readme__example__3()
    {
        $query = (new \Girgias\QueryBuilder\Select("demo"))
            ->where("author", "=", "author")
            ->whereOr("editor", "=", "editor")
            ->getQuery();

        $this->assertEquals(
            "SELECT * FROM demo WHERE (author = :author OR editor = :editor)",
            $query
        );
    }

    public function test__Readme__example__4()
    {
        $query = (new \Girgias\QueryBuilder\Update("posts"))
            ->where("id", SqlOperators::EQUAL, "id")
            ->bindField("title", "title")
            ->bindField("content", "content")
            ->bindField("date_last_edited", "now_date")
            ->getQuery();

        $this->assertEquals(
            "UPDATE posts SET title = :title, content = :content, date_last_edited = :now_date WHERE id = :id",
            $query
        );
    }
}
