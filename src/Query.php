<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Enums\SqlReservedWords;
use Girgias\QueryBuilder\Exceptions\DuplicateSqlParameter;
use Girgias\QueryBuilder\Exceptions\InvalidSqlParameterException;
use Girgias\QueryBuilder\Exceptions\InvalidSqlTableNameException;

/**
 * Class Query.
 */
abstract class Query
{
    protected const SQL_NAME_PATTERN = '/^[a-z][a-z0-9_]*(\.[a-z0-9_]+)*$/';
    protected const SQL_DATE_FORMAT = 'Y-m-d H:i:s';
    private const SQL_PARAMETER_PATTERN = '/^[a-zA-Z]+$/';

    /**
     * @var string
     */
    private $table;

    /**
     * @var array<string, mixed>
     */
    private $parameters = [];

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

    final public function getParameters(): array
    {
        return $this->parameters;
    }

    final protected function getTableName(): string
    {
        return $this->table;
    }

    /**
     * @param null|string $parameter
     * @param mixed       $value
     */
    final protected function addStatementParameter(?string $parameter, $value): void
    {
        if (\is_null($parameter)) {
            $parameter = $this->generateSqlParameter();
            if (\array_key_exists($parameter, $this->parameters)) {
                $this->addStatementParameter(null, $value);

                return;
            }
        }

        if (!$this->isValidSqlParameter($parameter)) {
            throw new InvalidSqlParameterException($parameter);
        }

        if (\array_key_exists($parameter, $this->parameters)) {
            throw new DuplicateSqlParameter($parameter);
        }

        $this->parameters[$parameter] = $value;
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

    final private function generateSqlParameter(): string
    {
        $string = '';
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < 10; ++$i) {
            $string .= \substr($chars, \mt_rand(0, \strlen($chars)), 1);
        }

        return $string;
    }

    final private function isValidSqlParameter(string $parameter): bool
    {
        if (1 === \preg_match(self::SQL_PARAMETER_PATTERN, $parameter)) {
            return true;
        }

        return false;
    }
}
