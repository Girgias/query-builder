<?php

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Select;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    /** SELECT QUERIES */
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

    /**
     * README EXAMPLES
     */
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
