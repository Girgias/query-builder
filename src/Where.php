<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder;

use DateTimeInterface;
use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlOperatorException;
use InvalidArgumentException;
use RuntimeException;
use TypeError;

abstract class Where extends Query
{
    private const TYPE_NORMAL = '';
    private const TYPE_NOT = 'NOT ';

    /**
     * @var ?array<int, string>
     */
    private $where;

    /**
     * Add a WHERE clause to the Query.
     *
     * @param string      $column
     * @param string      $operator
     * @param mixed       $value
     * @param null|string $parameter
     *
     * @return self
     */
    final public function where(string $column, string $operator, $value, ?string $parameter = null): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE', $column);
        }

        if (!SqlOperators::isValidValue($operator)) {
            throw new UnexpectedSqlOperatorException('WHERE', $operator);
        }

        $parameter = $this->addStatementParameter($parameter, $value);

        $this->where[] = $column.' '.$operator.' :'.$parameter;

        return $this;
    }

    /**
     * Add a WHERE clause to the Query which should be ORed with the previous use of a WHERE clause.
     *
     * @param string      $column
     * @param string      $operator
     * @param mixed       $value
     * @param null|string $parameter
     *
     * @return self
     */
    final public function whereOr(string $column, string $operator, $value, ?string $parameter = null): self
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

        $parameter = $this->addStatementParameter($parameter, $value);

        $this->where[] = '('.\array_pop($this->where).' OR '.
            $column.' '.$operator.' :'.$parameter.')';

        return $this;
    }

    final public function whereIsNull(string $column): self
    {
        $this->where[] = $this->buildIsNullClause(self::TYPE_NORMAL, $column);

        return $this;
    }

    final public function whereIsNotNull(string $column): self
    {
        $this->where[] = $this->buildIsNullClause(self::TYPE_NOT, $column);

        return $this;
    }

    final public function whereOrIsNull(string $column): self
    {
        $this->where[] = $this->buildOrIsNullClause(self::TYPE_NORMAL, $column);

        return $this;
    }

    final public function whereOrIsNotNull(string $column): self
    {
        $this->where[] = $this->buildOrIsNullClause(self::TYPE_NOT, $column);

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
        $this->where[] = $this->buildLikeClause(self::TYPE_NORMAL, $column, $pattern, $escapeChar, $namedParameter);

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
        $this->where[] = $this->buildLikeClause(self::TYPE_NOT, $column, $pattern, $escapeChar, $namedParameter);

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
        $this->where[] = $this->buildBetweenClause(self::TYPE_NORMAL, $column, $start, $end);

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
        $this->where[] = $this->buildBetweenClause(self::TYPE_NOT, $column, $start, $end);

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  ...$values
     *
     * @return self
     */
    final public function whereIn(string $column, ...$values): self
    {
        $this->where[] = $this->buildInClause(self::TYPE_NORMAL, $column, $values);

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  ...$values
     *
     * @return self
     */
    final public function whereNotIn(string $column, ...$values): self
    {
        $this->where[] = $this->buildInClause(self::TYPE_NOT, $column, $values);

        return $this;
    }

    final protected function getWhereClause(): ?array
    {
        return $this->where;
    }

    final private function buildIsNullClause(string $type, string $column): string
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE', $column);
        }

        return $column.' IS '.$type.'NULL';
    }

    final private function buildOrIsNullClause(string $type, string $column): string
    {
        if (\is_null($this->where)) {
            throw new RuntimeException('Need to define at least another WHERE clause before utilizing whereOr method');
        }

        return '('.\array_pop($this->where).' OR '.$this->buildIsNullClause($type, $column).')';
    }

    final private function buildLikeClause(
        string $type,
        string $column,
        string $pattern,
        ?string $escapeChar,
        ?string $namedParameter
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
     * @param string $type
     * @param string $column
     * @param mixed  $start
     * @param mixed  $end
     *
     * @return string
     */
    final private function buildBetweenClause(string $type, string $column, $start, $end): string
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

    /**
     * @param string            $type
     * @param string            $column
     * @param array<int, mixed> $values
     *
     * @return string
     */
    final private function buildInClause(string $type, string $column, array $values): string
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('WHERE '.$type.'IN', $column);
        }

        if (empty($values)) {
            throw new \ArgumentCountError('At least one value needs to be passed to WHERE '.$type.'IN clause.');
        }

        $parameters = [];

        /**
         * Suppressing psalm error as mixed values are expected.
         *
         * @psalm-suppress MixedAssignment
         */
        foreach ($values as $value) {
            $parameters[] = ':'.$this->addStatementParameter(null, $value);
        }

        return $column.' '.$type.'IN ('.\implode(', ', $parameters).')';
    }
}
