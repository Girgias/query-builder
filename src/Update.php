<?php
namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;
use RuntimeException;

class Update extends Query
{
    /**
     * Return built Query
     *
     * @return string
     */
    public function getQuery(): string
    {
        if (is_null($this->parameter)) {
            throw new RuntimeException("No fields to update defined");
        }
        if (is_null($this->where)) {
            throw new DangerousSqlQueryWarning('No WHERE clause in UPDATE query');
        }

        $parts = ['UPDATE'];
        $parts[] = $this->table;
        $parts[] = 'SET';

        $columns = [];

        foreach ($this->parameter as $column => $binding) {
            $columns[] = $column . ' = :' . $binding;
        }
        $parts[] = join(', ', $columns);

        $parts[] = 'WHERE';
        $parts[] = join(' AND ', $this->where);

        return join(' ', $parts);
    }
}
