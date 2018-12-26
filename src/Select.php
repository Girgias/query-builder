<?php

namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use Girgias\QueryBuilder\Exceptions\InvalidSqlAliasNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlFieldNameException;
use Girgias\QueryBuilder\Exceptions\UnexpectedSqlFunctionException;
use InvalidArgumentException;
use OutOfRangeException;

class Select extends Query
{
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
     * SELECT fields
     *
     * @param string ...$fields
     * @return Select
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
     * @return Select
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
     * @return Select
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

    final public function selectAll(): self
    {
        if (is_null($this->select)) {
            $this->select = [];
        }

        array_unshift($this->select, '*');
        return $this;
    }


    /**
     * SELECT DISTINCT fields
     *
     * @param string ...$fields
     * @return Select
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
     * @return Select
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
     * @return Select
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
