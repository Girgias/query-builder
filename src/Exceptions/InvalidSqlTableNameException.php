<?php

namespace Girgias\QueryBuilder\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Class InvalidSqlTableNameException
 * @package Girgias\QueryBuilder\Exceptions
 */
class InvalidSqlTableNameException extends InvalidArgumentException
{
    /**
     * InvalidSqlTableNameException constructor.
     * @param string $table
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $table, int $code = 0, ?Throwable $previous = null)
    {
        $message = "Table name `{$table}` is invalid.";
        parent::__construct($message, $code, $previous);
    }
}
