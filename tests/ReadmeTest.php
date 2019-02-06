<?php

declare(strict_types=1);

namespace Girgias\Tests\QueryBuilder;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ReadmeTest extends TestCase
{
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
            ->where('author', '=', 'Alice', 'author')
            ->whereOr('editor', '=', 'Alice', 'editor')
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
            ->where('id', '=', 1, 'id')
            ->bindField('title', 'This is a title', 'title')
            ->bindField('content', 'Hello World', 'content')
            ->bindField('date_last_edited', '2019-02-06', 'nowDate')
            ->getQuery()
        ;

        static::assertSame(
            'UPDATE posts SET title = :title, content = :content, date_last_edited = :nowDate WHERE id = :id',
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
