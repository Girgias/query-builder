<?php

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\AggregateFunctions;
use Girgias\QueryBuilder\Query;
use Girgias\QueryBuilder\SqlOperators;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function test__Delete__Query()
    {
        $query = (new Query(Query::QUERY_DELETE, 'posts'))
            ->where('id', SqlOperators::EQUAL, 'id');
        $this->assertEquals('DELETE FROM posts WHERE id = :id', $query->getQuery());
    }

    public function test__Insert__Query__With__One__Parameter()
    {
        $query = (new Query(Query::QUERY_INSERT, 'posts'))
            ->where('id', SqlOperators::EQUAL, 'id')
            ->bindField('username', 'username');
        $this->assertEquals('INSERT INTO posts (username) VALUES (:username)', $query->getQuery());
    }

    public function test__Insert__Query__With__Two__Parameter()
    {
        $query = (new Query(Query::QUERY_INSERT, 'posts'))
            ->bindField('username', 'username')
            ->bindField('age', 'age');
        $this->assertEquals('INSERT INTO posts (username, age) VALUES (:username, :age)', $query->getQuery());
    }

    public function test__Update__Query__With__One__Parameter()
    {
        $query = (new Query(Query::QUERY_UPDATE, 'posts'))
            ->where('id', SqlOperators::EQUAL, 'id')
            ->bindField('username', 'username');
        $this->assertEquals('UPDATE posts SET username = :username WHERE id = :id', $query->getQuery());
    }

    public function test__Update__Query__With__Two__Parameter()
    {
        $query = (new Query(Query::QUERY_UPDATE, 'posts'))
            ->where('id', SqlOperators::EQUAL, 'id')
            ->bindField('username', 'username')
            ->bindField('age', 'age');
        $this->assertEquals('UPDATE posts SET username = :username, age = :age WHERE id = :id', $query->getQuery());
    }

    /** SELECT QUERIES */
    public function test__Simple__Select__Query()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->select('title');
        $this->assertEquals('SELECT title FROM posts', $query->getQuery());
    }

    public function test__Query__Select()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'));
        $this->assertEquals('SELECT * FROM posts', $query->getQuery());
    }

    public function test__Query__Multiple__Select()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->select('title', 'category');
        $this->assertEquals('SELECT title, category FROM posts', $query->getQuery());
    }

    public function test__Query__Select__Column__Alias()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
        ->selectAs('title', 't');
        $this->assertEquals('SELECT title AS t FROM posts', $query->getQuery());
    }

    public function test__Query__Multiple__Distinct()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->distinct('title', 'category');
        $this->assertEquals('SELECT DISTINCT title, category FROM posts', $query->getQuery());
    }

    public function test__Query__Distinct__Column__Alias()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->distinctAs('title', 't');
        $this->assertEquals('SELECT DISTINCT title AS t FROM posts', $query->getQuery());
    }

    public function test__Select__Aggregate()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->selectAggregate('title', AggregateFunctions::COUNT, 'nb_titles');
        $this->assertEquals('SELECT COUNT(title) AS nb_titles FROM posts', $query->getQuery());
    }

    public function test__Select__Aggregate__and__normal__select()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->selectAggregate('title', AggregateFunctions::COUNT, 'nb_titles')
            ->select('category');
        $this->assertEquals('SELECT COUNT(title) AS nb_titles, category FROM posts', $query->getQuery());
    }

    public function test__Select__Aggregate__Distinct()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->distinctAggregate('title', AggregateFunctions::COUNT, 'nb_titles');
        $this->assertEquals('SELECT COUNT(DISTINCT title) AS nb_titles FROM posts', $query->getQuery());
    }

    public function test__Select__Aggregate__Distinct__and__normal__select()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->distinctAggregate('title', AggregateFunctions::COUNT, 'nb_titles')
            ->select('category');
        $this->assertEquals('SELECT COUNT(DISTINCT title) AS nb_titles, category FROM posts', $query->getQuery());
    }

    /** SELECT CLAUSES */
    public function test__Query__Where__Or()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->where('author', SqlOperators::EQUAL, 'demo1')
            ->whereOr('author', SqlOperators::EQUAL, 'demo2');
        $this->assertEquals('SELECT * FROM posts WHERE (author = :demo1 OR author = :demo2)', $query->getQuery());
    }

    public function test__Query__Where_And__Or()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->where('author', SqlOperators::EQUAL, 'demo1')
            ->whereOr('author', SqlOperators::EQUAL, 'demo2')
            ->where('published', SqlOperators::EQUAL, 'status');
        $this->assertEquals(
            'SELECT * FROM posts WHERE (author = :demo1 OR author = :demo2) AND published = :status',
            $query->getQuery()
        );
    }

    public function test__Query__Where__Not__Like__With__Escape__Char()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->whereNotLike('tags', '%UTF#_8', '#');
        $this->assertEquals("SELECT * FROM posts WHERE tags NOT LIKE '%UTF#_8' ESCAPE '#'", $query->getQuery());
    }

    public function test__Query__Where__Between__Integers()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->whereBetween('field', 5, 10);
        $this->assertEquals('SELECT * FROM posts WHERE field BETWEEN 5 AND 10', $query->getQuery());
    }
    public function test__Query__Where__Between__Dates()
    {
        $startDate = new \DateTime('01/01/2016');
        $endDate = new \DateTime('01/01/2017');
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->whereBetween('field', $startDate, $endDate);
        $this->assertEquals(
            'SELECT * FROM posts WHERE field BETWEEN \'2016-01-01 00:00:00\' AND \'2017-01-01 00:00:00\'',
            $query->getQuery()
        );
    }

    public function test__Query__Where__Not__Between__Integers()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->whereNotBetween('field', 5, 10);
        $this->assertEquals('SELECT * FROM posts WHERE field NOT BETWEEN 5 AND 10', $query->getQuery());
    }

    public function test__Query__Where__Not__Between__Dates()
    {
        $startDate = new \DateTime('01/01/2016');
        $endDate = new \DateTime('01/01/2017');
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->whereNotBetween('field', $startDate, $endDate);
        $this->assertEquals(
            'SELECT * FROM posts WHERE field NOT BETWEEN \'2016-01-01 00:00:00\' AND \'2017-01-01 00:00:00\'',
            $query->getQuery()
        );
    }

    public function test__Query__Having__Or()
    {
        $query = (new Query(Query::QUERY_SELECT, 'demo'))
            ->having('score', AggregateFunctions::MAX, SqlOperators::MORE_THAN_OR_EQUAL, 500)
            ->havingOr('score', AggregateFunctions::AVERAGE, SqlOperators::MORE_THAN, 200);
        $this->assertEquals('SELECT * FROM demo HAVING (MAX(score) >= 500 OR AVG(score) > 200)', $query->getQuery());
    }

    public function test__Query__Having__And__Or()
    {
        $query = (new Query(Query::QUERY_SELECT, 'demo'))
            ->having('score', AggregateFunctions::MAX, SqlOperators::MORE_THAN_OR_EQUAL, 500)
            ->havingOr('score', AggregateFunctions::AVERAGE, SqlOperators::MORE_THAN, 200)
            ->having('game_time', AggregateFunctions::MAX, SqlOperators::LESS_THAN, 180);
        $this->assertEquals(
            'SELECT * FROM demo HAVING (MAX(score) >= 500 OR AVG(score) > 200) AND MAX(game_time) < 180',
            $query->getQuery()
        );
    }

    public function test__Query__Select__With__Most__Methods()
    {
        $query = (new Query(Query::QUERY_SELECT, 'posts'))
            ->tableAlias('p')
            ->distinct('title')
            ->limit(15, 5)
            ->where('published', SqlOperators::EQUAL, 'status')
            ->having('score', AggregateFunctions::AVERAGE, SqlOperators::MORE_THAN, 20)
            ->whereLike('author', "John%")
            ->order('date_creation')
            ->group('id');

        $this->assertEquals(
            "SELECT DISTINCT title FROM posts AS p WHERE published = :status AND author LIKE 'John%' GROUP BY id HAVING AVG(score) > 20 ORDER BY date_creation ASC LIMIT 15 OFFSET 5",
            $query->getQuery()
        );
    }

    public function test__Readme__example__1()
    {
        $query = (new \Girgias\QueryBuilder\Query('select', 'demo'))
            ->limit(10, 20)
            ->order('published_date')
            ->getQuery();

        $this->assertEquals(
            "SELECT * FROM demo ORDER BY published_date ASC LIMIT 10 OFFSET 20",
            $query
        );
    }

    public function test__Readme__example__2()
    {
        $start = new \DateTime('01/01/2016');
        $end = new \DateTime('01/01/2017');
        $query = (new \Girgias\QueryBuilder\Query('select', 'demo'))
            ->select('title', 'slug')
            ->selectAs('name_author_post', 'author')
            ->whereBetween('date_published', $start, $end)
            ->order('date_published', 'DESC')
            ->limit(25)
            ->getQuery();

        $this->assertEquals(
            "SELECT title, slug, name_author_post AS author FROM demo WHERE date_published BETWEEN '2016-01-01 00:00:00' AND '2017-01-01 00:00:00' ORDER BY date_published DESC LIMIT 25",
            $query
        );
    }

    public function test__Readme__example__3()
    {
        $query = (new \Girgias\QueryBuilder\Query('select', 'demo'))
            ->where('author', '=', 'author')
            ->whereOr('editor', '=', 'editor')
            ->getQuery();
        $this->assertEquals(
            "SELECT * FROM demo WHERE (author = :author OR editor = :editor)",
            $query
        );
    }

    public function test__Readme__example__4()
    {
        $query = (new \Girgias\QueryBuilder\Query(Query::QUERY_UPDATE, 'posts'))
            ->where('id', SqlOperators::EQUAL, 'id')
            ->bindField('title', 'title')
            ->bindField('content', 'content')
            ->bindField('date_last_edited', 'now_date')->getQuery();
        $this->assertEquals(
            'UPDATE posts SET title = :title, content = :content, date_last_edited = :now_date WHERE id = :id',
            $query
        );
    }
}
