<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Class InvalidSqlFieldNameException.
 */
class InvalidSqlFieldNameException extends InvalidArgumentException
{
    /**
     * InvalidSqlFieldNameException constructor.
     *
     * @param string         $field
     * @param int            $code
     * @param null|Throwable $previous
     */
    public function __construct(string $field, int $code = 0, ?Throwable $previous = null)
    {
        $message = "Field name `{$field}` provided to the statement is invalid.";
        parent::__construct($message, $code, $previous);
    }
}
