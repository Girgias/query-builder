<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder;

use Girgias\QueryBuilder\Traits\BindField;
use RuntimeException;

final class Insert extends Query
{
    use BindField;

    /**
     * Return built Query.
     *
     * @return string
     */
    final public function getQuery(): string
    {
        if (\is_null($this->parameters)) {
            throw new RuntimeException('No fields to update defined');
        }

        $parts = ['INSERT INTO'];
        $parts[] = $this->table;

        $columns = \array_keys($this->parameters);
        $parts[] = '('.\implode(', ', $columns).')';

        $parts[] = 'VALUES';

        $parameters = [];
        foreach ($this->parameters as $parameter) {
            $parameters[] = ':'.$parameter;
        }
        $parts[] = '('.\implode(', ', $parameters).')';

        return \implode(' ', $parts);
    }
}
