<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder\Traits;

use Girgias\QueryBuilder\Exceptions\InvalidSqlFieldNameException;

trait BindField
{
    /**
     * @var ?array<string, string>
     */
    private $fields;

    /**
     * Binds a field to a parameter.
     *
     * @param string $field
     * @param mixed  $value
     * @param string $namedParameter
     *
     * @return self
     */
    final public function bindField(string $field, $value, ?string $namedParameter = null): self
    {
        if (!$this->isValidSqlName($field)) {
            throw new InvalidSqlFieldNameException($field);
        }

        $namedParameter = $this->addStatementParameter($namedParameter, $value);

        $this->fields[$field] = $namedParameter;

        return $this;
    }

    abstract protected function addStatementParameter(?string $parameter, $value): string;

    abstract protected function isValidSqlName(string $name): bool;
}
