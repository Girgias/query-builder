<?php

namespace Girgias\QueryBuilder\Traits;

use Girgias\QueryBuilder\Exceptions\InvalidSqlFieldNameException;

trait BindField
{
    /**
     * @var ?array<string, string>
     */
    protected $parameters;

    abstract protected function isValidSqlName(string $name): bool;

    /**
     * Binds a field to a parameter
     *
     * @param string $field
     * @param string $parameter
     * @return self
     */
    final public function bindField(string $field, string $parameter): self
    {
        if (!$this->isValidSqlName($field)) {
            throw new InvalidSqlFieldNameException($field);
        }

        $this->parameters[$field] = $parameter;

        return $this;
    }
}