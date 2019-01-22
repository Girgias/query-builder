<?php
declare(strict_types=1);

namespace Girgias\QueryBuilder\Enums;

use Girgias\Enums\Base;

/**
 * Class AggregateFunctions
 * @package Girgias\QueryBuilder
 */
abstract class AggregateFunctions extends Base
{
    const AVERAGE = 'AVG';
    const COUNT = 'COUNT';
    const MAX = 'MAX';
    const MIN = 'MIN';
    const SUM = 'SUM';
}
