<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use Girgias\QueryBuilder\Traits\BindField;
use Girgias\QueryBuilder\Traits\Where;
use RuntimeException;

final class Update extends Query
{
    use Where, BindField;

    /**
     * Return built Query.
     *
     * @return string
     */
    public function getQuery(): string
    {
        if (\is_null($this->parameters)) {
            throw new RuntimeException('No fields to update defined');
        }
        if (\is_null($this->where)) {
            throw new DangerousSqlQueryWarning('No WHERE clause in UPDATE query');
        }

        $parts = ['UPDATE'];
        $parts[] = $this->table;
        $parts[] = 'SET';

        $columns = [];

        foreach ($this->parameters as $column => $binding) {
            $columns[] = $column.' = :'.$binding;
        }
        $parts[] = \implode(', ', $columns);

        $parts[] = 'WHERE';
        $parts[] = \implode(' AND ', $this->where);

        return \implode(' ', $parts);
    }
}
