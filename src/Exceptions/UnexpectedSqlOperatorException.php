<?php
namespace Girgias\QueryBuilder\Exceptions;

use Throwable;
use UnexpectedValueException;

/**
 * Class UndefinedSqlOperatorException
 * @package Girgias\QueryBuilder\Exceptions
 */
class UnexpectedSqlOperatorException extends UnexpectedValueException
{
    /**
     * UndefinedSqlOperatorException constructor.
     * @param string $clause
     * @param string $operator
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $clause, string $operator, int $code = 0, ?Throwable $previous = null)
    {
        $message = "Comparison operator `{$operator}` provided for {$clause} clause is invalid or unsupported.";
        parent::__construct($message, $code, $previous);
    }
}
