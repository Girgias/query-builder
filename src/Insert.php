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
        if (\is_null($this->fields)) {
            throw new RuntimeException('No fields to update defined');
        }

        $parts = ['INSERT INTO'];
        $parts[] = $this->getTableName();

        $columns = \array_keys($this->fields);
        $parts[] = '('.\implode(', ', $columns).')';

        $parts[] = 'VALUES';

        $namedParameters = [];
        foreach ($this->fields as $namedParameter) {
            $namedParameters[] = ':'.$namedParameter;
        }

        $parts[] = '('.\implode(', ', $namedParameters).')';

        return \implode(' ', $parts);
    }
}
