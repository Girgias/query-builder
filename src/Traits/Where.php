<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder\Traits;

use DateTimeInterface;
use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlOperatorException;
use Girgias\QueryBuilder\Query;
use InvalidArgumentException;
use RuntimeException;
use TypeError;

trait Where
{
    /**
     * @var ?array<int, string>
     */
    private $where;

    /**
     * Add a WHERE clause to the Query.
     *
     * @param string $column
     * @param string $operator
     * @param string $parameter
     *
     * @return self
     */
    final public function where(string $column, string $operator, string $parameter): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE', $column);
        }

        if (!SqlOperators::isValidValue($operator)) {
            throw new UnexpectedSqlOperatorException('WHERE', $operator);
        }

        $this->where[] = $column.' '.$operator.' :'.$parameter;

        return $this;
    }

    /**
     * Add a WHERE clause to the Query which should be ORed with the previous use of a WHERE clause.
     *
     * @param string $column
     * @param string $operator
     * @param string $parameter
     *
     * @return self
     */
    final public function whereOr(string $column, string $operator, string $parameter): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE', $column);
        }

        if (!SqlOperators::isValidValue($operator)) {
            throw new UnexpectedSqlOperatorException('WHERE', $operator);
        }

        if (\is_null($this->where)) {
            throw new RuntimeException('Need to define at least another WHERE clause before utilizing whereOr method');
        }

        $this->where[] = '('.\array_pop($this->where).' OR '.
            $column.' '.$operator.' :'.$parameter.')';

        return $this;
    }

    /**
     * Add a WHERE LIKE clause to the Query.
     *
     * @param string      $column
     * @param string      $pattern
     * @param null|string $escapeChar
     * @param null|string $namedParameter
     *
     * @return self
     */
    final public function whereLike(
        string $column,
        string $pattern,
        ?string $escapeChar = null,
        ?string $namedParameter = null
    ): self {
        $this->where[] = $this->buildLikeClause($column, $pattern, $escapeChar, $namedParameter, '');

        return $this;
    }

    /**
     * Add a WHERE NOT LIKE clause to the Query.
     *
     * @param string      $column
     * @param string      $pattern
     * @param null|string $escapeChar
     * @param null|string $namedParameter
     *
     * @return self
     */
    final public function whereNotLike(
        string $column,
        string $pattern,
        ?string $escapeChar = null,
        ?string $namedParameter = null
    ): self {
        $this->where[] = $this->buildLikeClause($column, $pattern, $escapeChar, $namedParameter, 'NOT ');

        return $this;
    }

    /**
     * Add a WHERE BETWEEN clause to the Query.
     *
     * @param string                      $column
     * @param DateTimeInterface|float|int $start
     * @param DateTimeInterface|float|int $end
     *
     * @return self
     */
    final public function whereBetween(string $column, $start, $end): self
    {
        $this->where[] = $this->buildBetweenClause($column, $start, $end, '');

        return $this;
    }

    /**
     * Add a WHERE NOT BETWEEN clause to the Query.
     *
     * @param string                      $column
     * @param DateTimeInterface|float|int $start
     * @param DateTimeInterface|float|int $end
     *
     * @return self
     */
    final public function whereNotBetween(string $column, $start, $end): self
    {
        $this->where[] = $this->buildBetweenClause($column, $start, $end, 'NOT ');

        return $this;
    }

    abstract protected function addStatementParameter(?string $parameter, $value): string;

    abstract protected function isValidSqlName(string $name): bool;

    final private function buildLikeClause(
        string $column,
        string $pattern,
        ?string $escapeChar = null,
        ?string $namedParameter = null,
        string $type = ''
    ): string {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE '.$type.'LIKE', $column);
        }

        $namedParameter = $this->addStatementParameter($namedParameter, $pattern);

        return $column.' '.$type.'LIKE :'.$namedParameter.$this->escape($escapeChar);
    }

    /**
     * @param null|string $escapeChar
     *
     * @return string
     */
    final private function escape(?string $escapeChar): string
    {
        if (\is_null($escapeChar)) {
            return '';
        }
        if (1 !== \strlen($escapeChar)) {
            throw new InvalidArgumentException('Escape character for LIKE clause must be of length 1');
        }

        return ' ESCAPE \''.$escapeChar.'\'';
    }

    /**
     * @param string $column
     * @param mixed  $start
     * @param mixed  $end
     * @param string $type
     *
     * @return string
     */
    final private function buildBetweenClause(string $column, $start, $end, string $type = ''): string
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE '.$type.'BETWEEN', $column);
        }

        if (\gettype($start) !== \gettype($end)) {
            throw new TypeError('Start and End values provided to WHERE '.$type.'BETWEEN are of different types');
        }

        if (!\is_int($start) && !\is_float($start) && !($start instanceof DateTimeInterface) &&
            !\is_int($end) && !\is_float($end) && !($end instanceof DateTimeInterface)
        ) {
            throw new InvalidArgumentException(
                'Values for WHERE '.$type.'BETWEEN clause must be an integer, float or a DateTimeInterface. '
                .'Input was of type:'.\gettype($start)
            );
        }

        if ($start instanceof DateTimeInterface && $end instanceof DateTimeInterface) {
            $start = '\''.$start->format(Query::SQL_DATE_FORMAT).'\'';
            $end = '\''.$end->format(Query::SQL_DATE_FORMAT).'\'';
        }

        return $column.' '.$type.'BETWEEN '.(string) $start.' AND '.(string) $end;
    }
}
