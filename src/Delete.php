<?php
namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Clauses\Where;
use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;

class Delete extends Query
{
    use Where;

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
        $parts[] = join(' AND ', $this->where);

        return join(' ', $parts);
    }
}
