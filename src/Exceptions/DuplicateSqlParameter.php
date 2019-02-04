<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder\Exceptions;

use InvalidArgumentException;
use Throwable;

class DuplicateSqlParameter extends InvalidArgumentException
{
    /**
     * DuplicateSqlParameter constructor.
     *
     * @param string         $parameter
     * @param int            $code
     * @param null|Throwable $previous
     */
    public function __construct(string $parameter, int $code = 0, ?Throwable $previous = null)
    {
        $message = "SQL parameter `{$parameter}` provided to the statement has already been provided.";
        parent::__construct($message, $code, $previous);
    }
}
