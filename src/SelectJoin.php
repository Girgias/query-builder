<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Exceptions\InvalidSqlAliasNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlColumnNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlTableNameException;

final class SelectJoin extends Select
{
    /**
     * @var string
     */
    private $joinTable;

    /**
     * @var ?string
     */
    private $joinTableAlias;

    /**
     * @var ?string
     */
    private $joinType;

    /**
     * @var ?array<int, string>
     */
    private $joinOn;

    public function __construct(string $table, string $joinTable)
    {
        parent::__construct($table);

        if (!$this->isValidSqlName($joinTable)) {
            throw new InvalidSqlTableNameException($joinTable);
        }

        $this->joinTable = $joinTable;
    }

    final public function joinTableAlias(string $alias): self
    {
        if (!$this->isValidSqlName($alias)) {
            throw new InvalidSqlAliasNameException('JOIN TABLE ALIAS', $alias);
        }
        $this->joinTableAlias = $alias;

        return $this;
    }

    final public function crossJoin(): self
    {
        $this->joinType = 'CROSS';

        return $this;
    }

    final public function fullJoin(string $column, string $fkColumn): self
    {
        $this->joinOn($column, $fkColumn, 'FULL JOIN');

        $this->joinType = 'FULL';

        return $this;
    }

    final public function innerJoin(string $column, string $fkColumn): self
    {
        $this->joinType = 'INNER';

        $this->joinOn($column, $fkColumn, 'INNER JOIN');

        return $this;
    }

    final public function leftJoin(string $column, string $fkColumn): self
    {
        $this->joinType = 'LEFT';

        $this->joinOn($column, $fkColumn, 'LEFT JOIN');

        return $this;
    }

    final public function naturalJoin(): self
    {
        $this->joinType = 'NATURAL';

        return $this;
    }

    final public function rightJoin(string $column, string $fkColumn): self
    {
        $this->joinType = 'RIGHT';

        $this->joinOn($column, $fkColumn, 'RIGHT JOIN');

        return $this;
    }

    /**
     * Build SELECT query from parameters.
     *
     * @return string
     */
    final public function getQuery(): string
    {
        $parts = $this->buildBeginningSelectQuery();

        \array_push($parts, ...$this->buildJoinClause());

        \array_push($parts, ...$this->buildSqlClauses());

        return \implode(' ', $parts);
    }

    final private function joinOn(string $column, string $fkColumn, string $joinType): void
    {
        if (!$this->isValidSqlName($column)) {
            throw new InvalidSqlColumnNameException($joinType, $column);
        }
        if (!$this->isValidSqlName($fkColumn)) {
            throw new InvalidSqlColumnNameException($joinType, $fkColumn);
        }

        $this->joinOn = [
            'ON',
            $this->getTableName().'.'.$column,
            '=',
            $this->joinTable.'.'.$fkColumn,
        ];
    }

    final private function buildJoinClause(): array
    {
        if (\is_null($this->joinType)) {
            throw new \DomainException('Cannot build Join Clause without a selected join type');
        }

        $joinClause = [
            $this->joinType,
            'JOIN',
            $this->joinTable,
        ];

        if (!\is_null($this->joinTableAlias)) {
            $joinClause[] = 'AS';
            $joinClause[] = $this->joinTableAlias;
        }

        if ('NATURAL' !== $this->joinType && 'CROSS' !== $this->joinType) {
            \array_push($joinClause, ...$this->joinOn);
        }

        return $joinClause;
    }
}
