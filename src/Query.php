<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Enums\SqlReservedWords;
use Girgias\QueryBuilder\Exceptions\InvalidSqlTableNameException;

/**
 * Class Query.
 */
abstract class Query
{
    protected const SQL_NAME_PATTERN = '#^[a-z_]+(.[a-z0-9_])*$#';
    protected const SQL_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    private $table;

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
     * Return built Query.
     *
     * @return string
     */
    abstract public function getQuery(): string;

    final protected function getTableName(): string
    {
        return $this->table;
    }

    /**
     * Checks if argument is a valid SQL name.
     *
     * @param string $name
     *
     * @return bool
     */
    final protected function isValidSqlName(string $name): bool
    {
        if (1 === \preg_match(self::SQL_NAME_PATTERN, $name) &&
            !\in_array(\strtoupper($name), SqlReservedWords::RESERVED_WORDS, true)
        ) {
            return true;
        }

        return false;
    }
}
