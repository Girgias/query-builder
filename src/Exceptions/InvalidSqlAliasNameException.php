<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Class InvalidSqlAliasNameException.
 */
class InvalidSqlAliasNameException extends InvalidArgumentException
{
    /**
     * InvalidSqlAliasNameException constructor.
     *
     * @param string         $clauseOrField
     * @param string         $alias
     * @param int            $code
     * @param null|Throwable $previous
     */
    public function __construct(string $clauseOrField, string $alias, int $code = 0, ?Throwable $previous = null)
    {
        $message = "Alias `{$alias}` provided for {$clauseOrField} is invalid.";
        parent::__construct($message, $code, $previous);
    }
}
