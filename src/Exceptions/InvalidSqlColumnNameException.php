<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Class InvalidSqlColumnNameException.
 */
class InvalidSqlColumnNameException extends InvalidArgumentException
{
    /**
     * InvalidSqlColumnNameException constructor.
     *
     * @param string         $clause
     * @param string         $column
     * @param int            $code
     * @param null|Throwable $previous
     */
    public function __construct(string $clause, string $column, int $code = 0, ?Throwable $previous = null)
    {
        $message = "Column name `{$column}` provided for {$clause} clause is invalid.";
        parent::__construct($message, $code, $previous);
    }
}
