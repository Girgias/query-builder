<?php
declare(strict_types=1);

namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Enums\SqlReservedWords;
use Girgias\QueryBuilder\Exceptions\InvalidSqlFieldNameException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlTableNameException;

/**
 * Class Query
 * @package Girgias\QueryBuilder
 */
abstract class Query
{
    protected const SQL_NAME_PATTERN = '#^[a-z_]+(.[a-z0-9_])*$#';
    protected const SQL_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    protected $table;

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
}
