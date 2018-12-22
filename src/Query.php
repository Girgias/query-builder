<?php
declare(strict_types=1);

namespace Girgias\QueryBuilder;

use DateTimeInterface;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlFieldNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlTableNameException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlOperatorException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlFunctionException;
use InvalidArgumentException;
use RuntimeException;
use TypeError;

/**
 * Class Query
 * @package Girgias\QueryBuilder
 */
abstract class Query
{
    public const SORT_ASC = "ASC";
    public const SORT_DESC = "DESC";

    protected const SQL_NAME_PATTERN = "#^[a-z_]+(.[a-z0-9_])*$#";
    protected const SQL_DATE_FORMAT = "Y-m-d H:i:s";

    /**
     * @var string
     */
    protected $table;

    /**
     * @var ?array<int, string>
     */
    protected $where;

    /**
     * @var ?array<int, string>
     */
    protected $having;

    /**
     * @var ?array<string, string>
     */
    protected $parameter;

    /**
     * Query constructor.
     *
     * @param string $table
     */
    public function __construct(string $table)
    {
        if (!$this->isValidSqlName($table)) {
            throw new InvalidSqlTableNameException($table);
        }

        $this->table = $table;
    }

    /**
     * Add a WHERE clause to the Query
     *
     * @param string $column
     * @param string $operator
     * @param string $parameter
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * Add a HAVING clause to the Query
     *
     * @param string $column
     * @param string $aggregateFunction
     * @param string $operator
     * @param int $conditionValue
     * @return Query
     */
    final public function having(string $column, string $aggregateFunction, string $operator, int $conditionValue): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('HAVING', $column);
        }

        if (!AggregateFunctions::isValidValue($aggregateFunction)) {
            throw new UnexpectedSqlFunctionException('HAVING', $aggregateFunction);
        }

        if (!SqlOperators::isValidValue($operator)) {
            throw new UnexpectedSqlOperatorException('HAVING', $operator);
        }

        $this->having[] = $aggregateFunction . '(' . $column . ') ' . $operator . ' ' . $conditionValue;

        return $this;
    }

    /**
     * Add a HAVING clause to the Query which should be ORed with the previous use of a HAVING clause
     *
     * @param string $column
     * @param string $aggregateFunction
     * @param string $operator
     * @param int $conditionValue
     * @return Query
     */
    final public function havingOr(
        string $column,
        string $aggregateFunction,
        string $operator,
        int $conditionValue
    ): self {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('HAVING', $column);
        }

        if (!AggregateFunctions::isValidValue($aggregateFunction)) {
            throw new UnexpectedSqlFunctionException('HAVING', $aggregateFunction);
        }

        if (!SqlOperators::isValidValue($operator)) {
            throw new UnexpectedSqlOperatorException('HAVING', $operator);
        }

        if (is_null($this->having)) {
            throw new RuntimeException(
                'Need to define at least another HAVING clause before utilizing havingOr method'
            );
        }

        $this->having[] = '(' . array_pop($this->having) . ' OR ' .
            $aggregateFunction . '(' . $column . ') ' . $operator . ' ' . $conditionValue . ')';

        return $this;
    }

    /**
     * Binds a field to a parameter
     *
     * @param string $field
     * @param string $parameter
     * @return Query
     */
    final public function bindField(string $field, string $parameter): self
    {
        if (!$this->isValidSqlName($field)) {
            throw new InvalidSqlFieldNameException($field);
        }

        $this->parameter[$field] = $parameter;

        return $this;
    }

    /**
     * Return built Query
     *
     * @return string
     */
    abstract public function getQuery(): string;

    /**
     * Checks if argument is a valid SQL name
     *
     * @param string $name
     * @return bool
     */
    final protected function isValidSqlName(string $name): bool
    {
        if (preg_match(self::SQL_NAME_PATTERN, $name) === 1 &&
            !in_array(strtoupper($name), SqlReservedWords::RESERVED_WORDS)
        ) {
            return true;
        }
        return false;
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
            throw new TypeError("Start and End values provided to WHERE NOT BETWEEN are of different types");
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
            $start = '\'' . $start->format(self::SQL_DATE_FORMAT) . '\'';
            $end = '\'' . $end->format(self::SQL_DATE_FORMAT) . '\'';
        }

        return (string) $start . ' AND ' . (string) $end;
    }
}
