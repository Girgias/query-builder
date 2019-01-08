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
    protected $where;

    abstract protected function isValidSqlName(string $name): bool;

    /**
     * Add a WHERE clause to the Query
     *
     * @param string $column
     * @param string $operator
     * @param string $parameter
     * @return self
     */
    public function where(string $column, string $operator, string $parameter): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE', $column);
        }

        if (!SqlOperators::isValidValue($operator)) {
            throw new UnexpectedSqlOperatorException('WHERE', $operator);
        }

        $this->where[] = $column . ' ' . $operator . ' :' . $parameter;
        return $this;
    }

    /**
     * Add a WHERE clause to the Query which should be ORed with the previous use of a WHERE clause
     *
     * @param string $column
     * @param string $operator
     * @param string $parameter
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

        if (is_null($this->where)) {
            throw new RuntimeException('Need to define at least another WHERE clause before utilizing whereOr method');
        }

        $this->where[] = '(' . array_pop($this->where) . ' OR ' .
            $column . ' ' . $operator . ' :' . $parameter . ')';

        return $this;
    }

    /**
     * Add a WHERE LIKE clause to the Query
     *
     * @param string $column
     * @param string $pattern
     * @param string|null $escapeChar
     * @return self
     */
    final public function whereLike(string $column, string $pattern, ?string $escapeChar = null): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE LIKE', $column);
        }

        $this->where[] = $column . ' LIKE \'' . $pattern . '\'' . $this->escape($escapeChar);

        return $this;
    }

    /**
     * Add a WHERE NOT LIKE clause to the Query
     *
     * @param string $column
     * @param string $pattern
     * @param string|null $escapeChar
     * @return self
     */
    final public function whereNotLike(string $column, string $pattern, ?string $escapeChar = null): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE NOT LIKE', $column);
        }

        $this->where[] = $column . ' NOT LIKE \'' . $pattern . '\'' . $this->escape($escapeChar);

        return $this;
    }

    /**
     * Add a WHERE BETWEEN clause to the Query
     *
     * @param string $column
     * @param int|float|DateTimeInterface $start
     * @param int|float|DateTimeInterface $end
     * @return self
     */
    final public function whereBetween(string $column, $start, $end): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE BETWEEN', $column);
        }

        $this->where[] = $column . ' BETWEEN ' . $this->buildBetweenSqlString($start, $end);

        return $this;
    }

    /**
     * Add a WHERE NOT BETWEEN clause to the Query
     *
     * @param string $column
     * @param int|float|DateTimeInterface $start
     * @param int|float|DateTimeInterface $end
     * @return self
     */
    final public function whereNotBetween(string $column, $start, $end): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE NOT BETWEEN', $column);
        }

        $this->where[] = $column . ' NOT BETWEEN ' . $this->buildBetweenSqlString($start, $end);

        return $this;
    }

    /**
     * @param string|null $escapeChar
     * @return string
     */
    final private function escape(?string $escapeChar): string
    {
        if (is_null($escapeChar)) {
            return '';
        }
        if (mb_strlen($escapeChar) !== 1) {
            throw new InvalidArgumentException('Escape character for LIKE clause must be of length 1');
        }
        return ' ESCAPE \'' . $escapeChar . '\'';
    }

    /**
     * @param mixed $start
     * @param mixed $end
     * @return string
     */
    final private function buildBetweenSqlString($start, $end): string
    {
        if (gettype($start) !== gettype($end)) {
            throw new TypeError('Start and End values provided to WHERE NOT BETWEEN are of different types');
        }

        if (!is_int($start) && !is_float($start) && !($start instanceof DateTimeInterface) &&
            !is_int($end) && !is_float($end) && !($end instanceof DateTimeInterface)
        ) {
            throw new InvalidArgumentException(
                'Values for WHERE NOT BETWEEN clause must be an integer, float or a DateTimeInterface. '
                . 'Input was of type:' . gettype($start)
            );
        }

        if ($start instanceof DateTimeInterface && $end instanceof DateTimeInterface) {
            $start = '\'' . $start->format(Query::SQL_DATE_FORMAT) . '\'';
            $end = '\'' . $end->format(Query::SQL_DATE_FORMAT) . '\'';
        }

        return (string) $start . ' AND ' . (string) $end;
    }
}
