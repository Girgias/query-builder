<?php

namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Clauses\Where;
use Girgias\QueryBuilder\Enums\AggregateFunctions;
use Girgias\QueryBuilder\Enums\SqlOperators;
use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use Girgias\QueryBuilder\Exceptions\InvalidSqlAliasNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlFunctionException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlOperatorException;
use InvalidArgumentException;
use OutOfRangeException;
use RuntimeException;

class Select extends Query
{
    use Where;

    public const SORT_ASC = 'ASC';
    public const SORT_DESC = 'DESC';

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
    private $group;

    /**
     * @var ?array<int, string>
     */
    protected $having;

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
     * Set an alias for the table
     *
     * @param string $alias
     * @return Select
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
     * SELECT columns
     *
     * @param string ...$columns
     * @return Select
     */
    final public function select(string ...$columns): self
    {
        foreach ($columns as $column) {
            if (!$this->isValidSqlName($column)) {
                throw new InvalidSqlColumnNameException('SELECT', $column);
            }
            $this->select[] = $column;
        }
        return $this;
    }

    /**
     * SELECT a column with an alias
     *
     * @param string $column
     * @param string $alias
     * @return Select
     */
    final public function selectAs(string $column, string $alias): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('SELECT', $column);
        }

        if (!$this->isValidSqlName($alias)) {
            throw new InvalidSqlAliasNameException($column, $alias);
        }

        $this->select[] = $column . ' AS ' . $alias;
        return $this;
    }

    /**
     * SELECT an aggregated column
     *
     * @param string $column
     * @param string $aggregateFunction
     * @param string $alias
     * @return Select
     */
    final public function selectAggregate(string $column, string $aggregateFunction, string $alias): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('SELECT', $column);
        }

        if (!AggregateFunctions::isValidValue($aggregateFunction)) {
            throw new UnexpectedSqlFunctionException('SELECT with aggregate function', $aggregateFunction);
        }

        if (!$this->isValidSqlName($alias)) {
            throw new InvalidSqlAliasNameException($column, $alias);
        }

        $this->select[] = $aggregateFunction . '(' . $column . ') AS ' . $alias;
        return $this;
    }

    final public function selectAll(): self
    {
        if (is_null($this->select)) {
            $this->select = [];
        }

        array_unshift($this->select, '*');
        return $this;
    }


    /**
     * SELECT DISTINCT columns
     *
     * @param string ...$columns
     * @return Select
     */
    final public function distinct(string ...$columns): self
    {
        $this->distinct = true;
        return $this->select(...$columns);
    }

    /**
     * SELECT DISTINCT a column with an alias
     *
     * @param string $column
     * @param string $alias
     * @return Select
     */
    final public function distinctAs(string $column, string $alias): self
    {
        $this->distinct = true;
        return $this->selectAs($column, $alias);
    }

    /**
     * SELECT an aggregated DISTINCT column
     *
     * @param string $column
     * @param string $aggregateFunction
     * @param string $alias
     * @return Select
     */
    final public function distinctAggregate(string $column, string $aggregateFunction, string $alias): self
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException('SELECT', $column);
        }

        if (!AggregateFunctions::isValidValue($aggregateFunction)) {
            throw new UnexpectedSqlFunctionException('SELECT DISTINCT with aggregate function', $aggregateFunction);
        }

        if (!$this->isValidSqlName($alias)) {
            throw new InvalidSqlAliasNameException($column, $alias);
        }

        $this->select[] = $aggregateFunction . '(DISTINCT ' . $column . ') AS ' . $alias;
        return $this;
    }

    /**
     * Add a GROUP BY clause to the Query
     *
     * @param string $column
     * @return Select
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
     * @return Select
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
     * @return Select
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
     * @return Select
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
     * @return Select
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
     * Build SELECT query from parameters
     *
     * @return string
     */
    final public function getQuery(): string
    {
        if (!is_null($this->limit) && is_null($this->order)) {
            throw new DangerousSqlQueryWarning(
                'When using LIMIT, it is important to use an ORDER BY clause that constrains the result rows ' .
                'into a unique order. Otherwise you will get an unpredictable subset of the query\'s rows.'
            );
        }

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

            if (!is_null($this->offset)) {
                $parts[] = 'OFFSET';
                $parts[] = $this->offset;
            }
        }

        return join(' ', $parts);
    }
}
