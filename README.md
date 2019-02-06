# SQL Query Builder
[![Build Status](https://travis-ci.org/Girgias/query-builder.svg?branch=master)](https://travis-ci.org/Girgias/query-builder)
[![Maintainability](https://api.codeclimate.com/v1/badges/e804486b68df4080cead/maintainability)](https://codeclimate.com/github/Girgias/query-builder/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/e804486b68df4080cead/test_coverage)](https://codeclimate.com/github/Girgias/query-builder/test_coverage)

A fluent SQL Query Builder which **ONLY** builds a valid SQL query with the SQL 
clauses it has been asked to provide.

## Installing

```shell
composer require girgias/query-builder
```

## Features

This Query Builder can build a variety of SQL queries which are database agnostic
because it uses the ANSI standardized syntax.

Every sort of query has its own class which extends from the base Query class,
They all have the same constructor signature which requires the table name 
on which to execute the query.

To do a SELECT query with table join use the ``SelectJoin`` class.
An example can be seen below on how to use this class.

### Examples
A basic select query:
```php
$query = (new \Girgias\QueryBuilder\Select("demo"))
    ->limit(10, 20)
    ->order("published_date")
    ->getQuery();
```
Will output:
```sql
SELECT * FROM demo ORDER BY published_date ASC LIMIT 10 OFFSET 20
```

A more complex select query:
```php
$start = new \DateTime("01/01/2016");
$end   = new \DateTime("01/01/2017");
$query = (new \Girgias\QueryBuilder\Select("demo"))
    ->select("title", "slug")
    ->selectAs("name_author_post", "author")
    ->whereBetween("date_published", $start, $end)
    ->order("date_published", "DESC")
    ->limit(25)
    ->getQuery();
```

Will output:
```sql
SELECT title, slug, name_author_post AS author FROM demo WHERE date_published BETWEEN '2016-01-01 00:00:00' AND '2017-01-01 00:00:00' ORDER BY date_published DESC LIMIT 25
```

An example with the ``whereOr`` method:
```php
$query = (new \Girgias\QueryBuilder\Select("demo"))
    ->where("author", "=", "author")
    ->whereOr("editor", "=", "editor")
    ->getQuery();
```

Will output:
```sql
SELECT * FROM demo WHERE (author = :author OR editor = :editor)
```

Update example:
```php
$query = (new \Girgias\QueryBuilder\Update("posts"))
    ->where("id", '=', "id")
    ->bindField('title', 'This is a title', 'title')
    ->bindField('content', 'Hello World', 'content')
    ->bindField('date_last_edited', '2019-02-06', 'nowDate')
    ->getQuery();
```
Will output:
```sql
UPDATE posts SET title = :title, content = :content, date_last_edited = :now_date WHERE id = :id
```

Select INNER join example:
```php
$query = (new \Girgias\QueryBuilder\SelectJoin('comments', 'posts'))
    ->tableAlias('co')
    ->select('co.user', 'co.content', 'p.title')
    ->joinTableAlias('p')
    ->innerJoin('post_id', 'id')
    ->getQuery();
```
Will output:
```sql
SELECT co.user, co.content, p.title FROM comments AS co INNER JOIN posts AS p ON comments.post_id = posts.id
```

## ToDos

There are some features that are still waiting to be implementing

* WHERE IN and WHERE NOT IN clauses

## Contributing

If you found an invalid SQL name which **DOESN'T** throw a Runtime exception
or a valid SQL name which does please add a test case into the 
``tests/CheckSqlNamesTest.php`` file.

If you found an example where this library returns an invalid SQL query
please add (or fix) a test case in the relevant Query test case or if it's
a general error please use the ``tests/QueryTest`` file.

If a RunTime exception should be thrown please add a test in the 
``tests/QueryThrowExceptionsTest.php`` file if it's for general Exceptions
or if it is specific to the Select Query please add a test in the
``tests/SelectThrowExceptionsTest.php`` file.

If you'd like to contribute, please fork the repository and use a feature
branch. Pull requests are warmly welcome.

### Notes
When contributing you should make sure that Psalm runs without error
and all unit tests pass.
Moreover if you add functionality please add corresponding unit tests to cover
at least 90% of your code and that theses tests make sure of edge cases if they exist.

## Links

- Repository: https://github.com/girgias/query-builder/
- Issue tracker: https://github.com/girgias/query-builder/issues
  - In case of sensitive bugs like security vulnerabilities, please contact
    george.banyard@gmail.com directly instead of using the issue tracker.


## Licensing

The code in this project is licensed under MIT license.
