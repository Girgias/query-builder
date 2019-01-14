<?php
namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;

class Delete extends Query
{

    /**
     * Return built Query
     *
     * @return string
     */
    public function getQuery(): string
    {
        if (is_null($this->where)) {
            throw new DangerousSqlQueryWarning('No WHERE clause in DELETE FROM query');
        }

        $parts = ['DELETE FROM'];
        $parts[] = $this->table;

        $parts[] = 'WHERE';
        $parts[] = implode(' AND ', $this->where);

        return implode(' ', $parts);
    }
}
