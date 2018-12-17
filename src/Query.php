<?php
declare(strict_types=1);

namespace Girgias\QueryBuilder;

use DateTimeInterface;
use DomainException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlAliasNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlFieldNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlTableNameException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlOperatorException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlFunctionException;
use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use InvalidArgumentException;
use OutOfRangeException;
use RuntimeException;
use TypeError;

/**
 * Class Query
 * @package Girgias\QueryBuilder
 */
class Query
{
    public const QUERY_DELETE = "delete";
    public const QUERY_INSERT = "insert";
    public const QUERY_SELECT = "select";
    public const QUERY_UPDATE = "update";

    public const SORT_ASC = "ASC";
    public const SORT_DESC = "DESC";

    protected const SQL_NAME_PATTERN = "#^[a-z_]+(.[a-z0-9_])*$#";
    protected const SQL_DATE_FORMAT = "Y-m-d H:i:s";

    /**
     * @var string
     */
    private $queryType;

    /**
     * @var string
     */
    private $table;

    /**
     * @var ?string
     */
    private $tableAlias;

    /**
     * @var ?array<int, string>
     */
    private $select;

    /**
     * @var bool
     */
    private $distinct = false;

    /**
     * @var ?array<int, string>
     */
    private $where;

    /**
     * @var ?array<int, string>
     */
    private $group;

    /**
     * @var ?array<int, string>
     */
    private $having;

    /**
     * @var ?array<int, string>
     */
    private $order;

    /**
     * @var ?int
     */
    private $limit;

    /**
     * @var ?int
     */
    private $offset;

    /**
     * @var ?array<string, string>
     */
    private $parameter;

    /**
     * Query constructor.
     *
     * @param string $queryType
     * @param string $table
     */
    public function __construct(string $queryType, string $table)
    {
        if ($queryType !== self::QUERY_DELETE &&
            $queryType !== self::QUERY_INSERT &&
            $queryType !== self::QUERY_SELECT &&
            $queryType !== self::QUERY_UPDATE) {
            throw new InvalidArgumentException("Query type {$queryType} is invalid.");
        }

        if (!$this->isValidSqlName($table)) {
            throw new InvalidSqlTableNameException($table);
        }

        $this->queryType = $queryType;

        $this->table = $table;
    }

    /**
     * SELECT Query functions
     */

    /**
     * Set an alias for the table
     *
     * @param string $alias
     * @return Query
     */
    final public function tableAlias(string $alias): self
    {
        if (!$this->isValidSqlName($alias)) {
            throw new InvalidSqlAliasNameException('FROM', $alias);
        }
        $this->tableAlias = $alias;
        return $this;
    }

    /**
     * SELECT fields
     *
     * @param string ...$fields
     * @return Query
     */
    final public function select(string ...$fields): self
    {
        foreach ($fields as $field) {
            if (!$this->isValidSqlName($field)) {
                throw new InvalidSqlFieldNameException($field);
            }
            $this->select[] = $field;
        }
        return $this;
    }

    /**
     * SELECT a field with an alias
     *
     * @param string $field
     * @param string $alias
     * @return Query
     */
    final public function selectAs(string $field, string $alias): self
    {
        if (!$this->isValidSqlName($field)) {
            throw new InvalidSqlFieldNameException($field);
        }

        if (!$this->isValidSqlName($alias)) {
            throw new InvalidSqlAliasNameException($field, $alias);
        }

        $this->select[] = $field . ' AS ' . $alias;
        return $this;
    }

    /**
     * SELECT an aggregated field
     *
     * @param string $field
     * @param string $aggregateFunction
     * @param string $alias
     * @return Query
     */
    final public function selectAggregate(string $field, string $aggregateFunction, string $alias): self
    {
        if (!$this->isValidSqlName($field)) {
            throw new InvalidSqlFieldNameException($field);
        }

        if (!AggregateFunctions::isValidValue($aggregateFunction)) {
            throw new UnexpectedSqlFunctionException('SELECT with aggregate function', $aggregateFunction);
        }

        if (!$this->isValidSqlName($alias)) {
            throw new InvalidSqlAliasNameException($field, $alias);
        }

        $this->select[] = $aggregateFunction . '(' . $field . ') AS ' . $alias;
        return $this;
    }

    /**
     * SELECT DISTINCT fields
     *
     * @param string ...$fields
     * @return Query
     */
    final public function distinct(string ...$fields): self
    {
        $this->distinct = true;
        return $this->select(...$fields);
    }

    /**
     * SELECT DISTINCT a field with an alias
     *
     * @param string $field
     * @param string $alias
     * @return Query
     */
    final public function distinctAs(string $field, string $alias): self
    {
        $this->distinct = true;
        return $this->selectAs($field, $alias);
    }

    /**
     * SELECT an aggregated DISTINCT field
     *
     * @param string $field
     * @param string $aggregateFunction
     * @param string $alias
     * @return Query
     */
    final public function distinctAggregate(string $field, string $aggregateFunction, string $alias): self
    {
        if (!$this->isValidSqlName($field)) {
            throw new InvalidSqlFieldNameException($field);
        }

        if (!AggregateFunctions::isValidValue($aggregateFunction)) {
            throw new UnexpectedSqlFunctionException('SELECT DISTINCT with aggregate function', $aggregateFunction);
        }

        if (!$this->isValidSqlName($alias)) {
            throw new InvalidSqlAliasNameException($field, $alias);
        }

        $this->select[] = $aggregateFunction . '(DISTINCT ' . $field . ') AS ' . $alias;
        return $this;
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
     * Add a GROUP BY clause to the Query
     *
     * @param string $column
     * @return Query
     */
    final public function group(string $column): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('GROUP BY', $column);
        }

        $this->group = [$column];
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
     * Add an ORDER BY clause to the Query
     *
     * @param string $column
     * @param string $order
     * @return Query
     */
    final public function order(string $column, string $order = self::SORT_ASC): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('ORDER BY', $column);
        }
        if ($order !== self::SORT_ASC && $order !== self::SORT_DESC) {
            throw new InvalidArgumentException('Order must be ' . self::SORT_ASC . ' or ' . self::SORT_DESC);
        }

        $this->order[] = $column . ' ' . $order;
        return $this;
    }

    /**
     * Add a LIMIT clause to the Query
     *
     * @param int $limit
     * @param int|null $offset
     * @return Query
     */
    final public function limit(int $limit, ?int $offset = null): self
    {
        if ($limit < 0) {
            throw new OutOfRangeException('SQL LIMIT can\'t be less than 0');
        }
        $this->limit = $limit;
        if (!is_null($offset)) {
            $this->offset($offset);
        }

        return $this;
    }

    /**
     * Add an OFFSET clause to the Query
     *
     * @param int $offset
     */
    final private function offset(int $offset): void
    {
        if ($offset < 0) {
            throw new OutOfRangeException('SQL OFFSET can\'t be less than 0');
        }
        $this->offset = $offset;
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
    final public function getQuery(): string
    {
        switch ($this->queryType) {
            case self::QUERY_DELETE:
                return $this->buildDeleteQuery();
            case self::QUERY_INSERT:
                return $this->buildInsertQuery();
            case self::QUERY_SELECT:
                return $this->buildSelectQuery();
            case self::QUERY_UPDATE:
                return $this->buildUpdateQuery();
        }
        throw new DomainException('No valid query type to generate query');
    }

    /**
     * Build DELETE query from parameters
     *
     * @return string
     */
    final private function buildDeleteQuery(): string
    {
        if (is_null($this->where)) {
            throw new DangerousSqlQueryWarning('No WHERE clause in DELETE FROM query');
        }

        $parts = ['DELETE FROM'];
        $parts[] = $this->table;

        $parts[] = 'WHERE';
        $parts[] = join(' AND ', $this->where);

        return join(' ', $parts);
    }

    /**
     * Build INSERT INTO query from parameters
     *
     * @return string
     */
    final private function buildInsertQuery(): string
    {
        if (is_null($this->parameter)) {
            throw new RuntimeException("No fields to update defined");
        }

        $parts = ['INSERT INTO'];
        $parts[] = $this->table;

        $columns = [];
        foreach (array_keys($this->parameter) as $keys) {
            $columns[] = $keys;
        }
        $parts[] = '(' . join(', ', $columns) . ')';

        $parts[] = 'VALUES';

        $parameters = [];
        foreach ($this->parameter as $parameter) {
            $parameters[] = ':' . $parameter;
        }
        $parts[] = '(' . join(', ', $parameters) . ')';

        return join(' ', $parts);
    }

    /**
     * Build SELECT query from parameters
     *
     * @return string
     */
    final private function buildSelectQuery(): string
    {
        if (is_null($this->select)) {
            $this->select[] = '*';
        }
        $parts = ['SELECT'];
        if ($this->distinct) {
            $parts[] = 'DISTINCT';
        }
        $parts[] = join(', ', $this->select);

        $parts[] = 'FROM';
        $parts[] = $this->table;

        if (!is_null($this->tableAlias)) {
            $parts[] = 'AS';
            $parts[] = $this->tableAlias;
        }

        if (!is_null($this->where)) {
            $parts[] = 'WHERE';
            $parts[] = join(' AND ', $this->where);
        }

        if (!is_null($this->group)) {
            $parts[] = 'GROUP BY';
            $parts[] = join(' ', $this->group);
        }

        if (!is_null($this->having)) {
            $parts[] = 'HAVING';
            $parts[] = join(' AND ', $this->having);
        }

        if (!is_null($this->order)) {
            $parts[] = 'ORDER BY';
            $parts[] = join(', ', $this->order);
        }

        if (!is_null($this->limit)) {
            $parts[] = 'LIMIT';
            $parts[] = $this->limit;
        }

        if (!is_null($this->offset)) {
            $parts[] = 'OFFSET';
            $parts[] = $this->offset;
        }

        return join(' ', $parts);
    }

    /**
     * Build UPDATE query from parameters
     *
     * @return string
     */
    final private function buildUpdateQuery(): string
    {
        if (is_null($this->parameter)) {
            throw new RuntimeException("No fields to update defined");
        }
        if (is_null($this->where)) {
            throw new DangerousSqlQueryWarning('No WHERE clause in UPDATE query');
        }

        $parts = ['UPDATE'];
        $parts[] = $this->table;
        $parts[] = 'SET';

        $columns = [];
        
        foreach ($this->parameter as $column => $binding) {
            $columns[] = $column . ' = :' . $binding;
        }
        $parts[] = join(', ', $columns);

        $parts[] = 'WHERE';
        $parts[] = join(' AND ', $this->where);

        return join(' ', $parts);
    }

    /**
     * Checks if argument is a valid SQL name
     *
     * @param string $name
     * @return bool
     */
    final private function isValidSqlName(string $name): bool
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
