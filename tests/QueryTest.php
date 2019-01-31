<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Select;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class QueryTest extends TestCase
{
    /** SELECT QUERIES */
    public function testQueryWhereOr(): void
    {
        $query = (new Select('posts'))
            ->where('author', SqlOperators::EQUAL, 'demo1')
            ->whereOr('author', SqlOperators::EQUAL, 'demo2')
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM posts WHERE (author = :demo1 OR author = :demo2)', $query);
    }

    public function testQueryWhereAndOr(): void
    {
        $query = (new Select('posts'))
            ->where('author', SqlOperators::EQUAL, 'demo1')
            ->whereOr('author', SqlOperators::EQUAL, 'demo2')
            ->where('published', SqlOperators::EQUAL, 'status')
            ->getQuery()
        ;

        static::assertSame(
            'SELECT * FROM posts WHERE (author = :demo1 OR author = :demo2) AND published = :status',
            $query
        );
    }

    public function testQueryWhereNotLikeWithEscapeChar(): void
    {
        $query = (new Select('posts'))
            ->whereNotLike('tags', '%UTF#_8', '#')
            ->getQuery()
        ;

        static::assertSame("SELECT * FROM posts WHERE tags NOT LIKE '%UTF#_8' ESCAPE '#'", $query);
    }

    public function testQueryWhereBetweenIntegers(): void
    {
        $query = (new Select('posts'))
            ->whereBetween('field', 5, 10)
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM posts WHERE field BETWEEN 5 AND 10', $query);
    }

    public function testQueryWhereBetweenDates(): void
    {
        $startDate = new \DateTime('01/01/2016');
        $endDate = new \DateTime('01/01/2017');
        $query = (new Select('posts'))
            ->whereBetween('field', $startDate, $endDate)
            ->getQuery()
        ;

        static::assertSame(
            "SELECT * FROM posts WHERE field BETWEEN '2016-01-01 00:00:00' AND '2017-01-01 00:00:00'",
            $query
        );
    }

    public function testQueryWhereNotBetweenIntegers(): void
    {
        $query = (new Select('posts'))
            ->whereNotBetween('field', 5, 10)
            ->getQuery()
        ;

        static::assertSame('SELECT * FROM posts WHERE field NOT BETWEEN 5 AND 10', $query);
    }

    public function testQueryWhereNotBetweenDates(): void
    {
        $startDate = new \DateTime('01/01/2016');
        $endDate = new \DateTime('01/01/2017');
        $query = (new Select('posts'))
            ->whereNotBetween('field', $startDate, $endDate)
            ->getQuery()
        ;

        static::assertSame(
            "SELECT * FROM posts WHERE field NOT BETWEEN '2016-01-01 00:00:00' AND '2017-01-01 00:00:00'",
            $query
        );
    }

    /**
     * README EXAMPLES.
     */
    public function testReadmeExample1(): void
    {
        $query = (new \Girgias\QueryBuilder\Select('demo'))
            ->limit(10, 20)
            ->order('published_date')
            ->getQuery()
        ;

        static::assertSame(
            'SELECT * FROM demo ORDER BY published_date ASC LIMIT 10 OFFSET 20',
            $query
        );
    }

    public function testReadmeExample2(): void
    {
        $start = new \DateTime('01/01/2016');
        $end = new \DateTime('01/01/2017');
        $query = (new \Girgias\QueryBuilder\Select('demo'))
            ->select('title', 'slug')
            ->selectAs('name_author_post', 'author')
            ->whereBetween('date_published', $start, $end)
            ->order('date_published', 'DESC')
            ->limit(25)
            ->getQuery()
        ;

        static::assertSame(
            'SELECT title, slug, name_author_post AS author FROM demo WHERE date_published '.
                "BETWEEN '2016-01-01 00:00:00' AND '2017-01-01 00:00:00' ORDER BY date_published DESC LIMIT 25",
            $query
        );
    }

    public function testReadmeExample3(): void
    {
        $query = (new \Girgias\QueryBuilder\Select('demo'))
            ->where('author', '=', 'author')
            ->whereOr('editor', '=', 'editor')
            ->getQuery()
        ;

        static::assertSame(
            'SELECT * FROM demo WHERE (author = :author OR editor = :editor)',
            $query
        );
    }

    public function testReadmeExample4(): void
    {
        $query = (new \Girgias\QueryBuilder\Update('posts'))
            ->where('id', SqlOperators::EQUAL, 'id')
            ->bindField('title', 'title')
            ->bindField('content', 'content')
            ->bindField('date_last_edited', 'now_date')
            ->getQuery()
        ;

        static::assertSame(
            'UPDATE posts SET title = :title, content = :content, date_last_edited = :now_date WHERE id = :id',
            $query
        );
    }

    public function testReadmeExample5(): void
    {
        $query = (new \Girgias\QueryBuilder\SelectJoin('comments', 'posts'))
            ->tableAlias('co')
            ->select('co.user', 'co.content', 'p.title')
            ->joinTableAlias('p')
            ->innerJoin('post_id', 'id')
            ->getQuery()
        ;

        static::assertSame(
            'SELECT co.user, co.content, p.title FROM comments AS co INNER JOIN posts AS p ON comments.post_id = posts.id',
            $query
        );
    }
}
