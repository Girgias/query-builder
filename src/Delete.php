<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Exceptions\DangerousSqlQueryWarning;

final class Delete extends Where
{
    /**
     * Return built Query.
     *
     * @return string
     */
    public function getQuery(): string
    {
        $whereClause = $this->getWhereClause();
        if (\is_null($whereClause)) {
            throw new DangerousSqlQueryWarning('No WHERE clause in DELETE FROM query');
        }

        $parts = ['DELETE FROM'];
        $parts[] = $this->getTableName();

        $parts[] = 'WHERE';
        $parts[] = \implode(' AND ', $whereClause);

        return \implode(' ', $parts);
    }
}
