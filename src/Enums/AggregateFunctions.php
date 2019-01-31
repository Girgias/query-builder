<?php

declare(strict_types=1);

namespace Girgias\QueryBuilder\Enums;

use Girgias\Enums\Base;

/**
 * Class AggregateFunctions.
 */
abstract class AggregateFunctions extends Base
{
    const AVERAGE = 'AVG';
    const COUNT = 'COUNT';
    const MAX = 'MAX';
    const MIN = 'MIN';
    const SUM = 'SUM';
}
