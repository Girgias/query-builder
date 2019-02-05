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
     * @param string $namedParameter
     *
     * @return self
     */
    final public function bindField(string $field, string $namedParameter): self
    {
        if (!$this->isValidSqlName($field)) {
            throw new InvalidSqlFieldNameException($field);
        }

        $this->fields[$field] = $namedParameter;

        return $this;
    }

    abstract protected function addStatementParameter(?string $parameter, $value): void;

    abstract protected function isValidSqlName(string $name): bool;
}
