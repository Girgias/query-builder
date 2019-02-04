<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Class InvalidSqlParameter.
 */
class InvalidSqlParameterException extends InvalidArgumentException
{
    /**
     * InvalidSqlParameterException constructor.
     *
     * @param string         $parameter
     * @param int            $code
     * @param null|Throwable $previous
     */
    public function __construct(string $parameter, int $code = 0, ?Throwable $previous = null)
    {
        $message = "SQL parameter `{$parameter}` provided to the statement is invalid.";
        parent::__construct($message, $code, $previous);
    }
}
