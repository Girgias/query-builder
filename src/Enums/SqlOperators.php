<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder\Enums;

use Girgias\Enums\Base;

/**
 * Class SqlOperators.
 */
abstract class SqlOperators extends Base
{
    public const DIFFERENT = '<>';
    public const EQUAL = '=';
    public const LESS_THAN = '<';
    public const LESS_THAN_OR_EQUAL = '<=';
    public const MORE_THAN = '>';
    public const MORE_THAN_OR_EQUAL = '>=';
    public const IS = 'IS';
    public const IS_NOT = 'IS NOT';
}
