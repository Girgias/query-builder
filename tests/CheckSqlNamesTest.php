<?php
namespace Girgias\Tests\QueryBuilder;

use Girgias\QueryBuilder\Select;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CheckSqlNamesTest extends TestCase
{
    public function testIsValidSqlName()
    {
        $query = (new Select('demo'));
        $method = new ReflectionMethod($query, 'isValidSqlName');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($query, 'p'));
        $this->assertTrue($method->invoke($query, 'p.field'));
        $this->assertTrue($method->invoke($query, 'p.table.col'));
        $this->assertTrue($method->invoke($query, 'semi_long_table_name'));
        $this->assertTrue($method->invoke($query, 'semi_lo43g_table_n22e'));

        $this->assertFalse($method->invoke($query, '58semi_lo43g_table_n22e'));
        $this->assertFalse($method->invoke($query, '2col'));
        $this->assertFalse($method->invoke($query, 'p.'));
        $this->assertFalse($method->invoke($query, 'p.table.'));
    }
}
