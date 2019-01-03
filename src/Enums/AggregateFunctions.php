<?php
namespace Girgias\QueryBuilder\Enums;

/**
 * Class AggregateFunctions
 * @package Girgias\QueryBuilder
 */
abstract class AggregateFunctions extends BasicEnum
{
    const AVERAGE = 'AVG';
    const COUNT = 'COUNT';
    const MAX = 'MAX';
    const MIN = 'MIN';
    const SUM = 'SUM';
}
