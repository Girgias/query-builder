<?php

namespace Girgias\QueryBuilder;

use RuntimeException;

class Insert extends Query
{

    /**
     * Return built Query
     *
     * @return string
     */
    final public function getQuery(): string
    {
        if (is_null($this->parameter)) {
            throw new RuntimeException('No fields to update defined');
        }

        $parts = ['INSERT INTO'];
        $parts[] = $this->table;

        $columns = [];
        foreach (array_keys($this->parameter) as $keys) {
            $columns[] = $keys;
        }
        $parts[] = '(' . join(', ', $columns) . ')';

        $parts[] = 'VALUES';

        $parameters = [];
        foreach ($this->parameter as $parameter) {
            $parameters[] = ':' . $parameter;
        }
        $parts[] = '(' . join(', ', $parameters) . ')';

        return join(' ', $parts);
    }
}
