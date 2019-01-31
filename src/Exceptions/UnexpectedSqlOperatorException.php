<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder\Exceptions;

use Throwable;
use UnexpectedValueException;

/**
 * Class UndefinedSqlOperatorException.
 */
class UnexpectedSqlOperatorException extends UnexpectedValueException
{
    /**
     * UndefinedSqlOperatorException constructor.
     *
     * @param string         $clause
     * @param string         $operator
     * @param int            $code
     * @param null|Throwable $previous
     */
    public function __construct(string $clause, string $operator, int $code = 0, ?Throwable $previous = null)
    {
        $message = "Comparison operator `{$operator}` provided for {$clause} clause is invalid or unsupported.";
        if ('!=' === $operator) {
            $message .= "\nDid you mean `<>` (ANSI 'not equal to' operator) ?";
        }
        parent::__construct($message, $code, $previous);
    }
}
