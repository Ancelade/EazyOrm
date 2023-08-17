<?php

namespace Ancelade\EazyOrm\Tests;

use PHPUnit\Framework\TestCase;
use Ancelade\EazyOrm\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    public function testWhereCondition()
    {
        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $result = $queryBuilder->where('column1', '=', 'value1')->toSql('SELECT *');

        $expectedSql = 'SELECT * FROM table_name WHERE column1 = ?';
        $expectedBindings = ['value1'];

        $this->assertSame($expectedSql, $result['sql']);
        $this->assertSame($expectedBindings, $result['bindings']);
    }

    public function testWhereRawCondition()
    {
        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $result = $queryBuilder->whereRaw('column1 > ?', [42])->toSql('SELECT *');

        $expectedSql = 'SELECT * FROM table_name WHERE column1 > ?';
        $expectedBindings = [42];

        $this->assertSame($expectedSql, $result['sql']);
        $this->assertSame($expectedBindings, $result['bindings']);
    }

    public function testOrderBy()
    {
        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $result = $queryBuilder->orderBy('column1', 'DESC')->toSql('SELECT *');

        $expectedSql = 'SELECT * FROM table_name ORDER BY column1 DESC';

        $this->assertSame($expectedSql, $result['sql']);
    }

    public function testWhereIn()
    {
        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $result = $queryBuilder->whereIn('column1', [1, 2, 3])->toSql('SELECT *');

        $expectedSql = 'SELECT * FROM table_name WHERE column1 IN (?, ?, ?)';
        $expectedBindings = [1, 2, 3];

        $this->assertSame($expectedSql, $result['sql']);
        $this->assertSame($expectedBindings, $result['bindings']);
    }

    public function testGroupBy()
    {
        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $result = $queryBuilder->groupBy('column1')->toSql('SELECT *');

        $expectedSql = 'SELECT * FROM table_name GROUP BY column1';

        $this->assertSame($expectedSql, $result['sql']);
    }

    public function testFirst()
    {
        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $result = $queryBuilder->first();

        $this->assertSame('SELECT * FROM table_name LIMIT 1', $result['sql']);
    }

    public function testAll()
    {
        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $result = $queryBuilder->all(10);

        $this->assertSame('SELECT * FROM table_name LIMIT 10', $result['sql']);
    }

    public function testCount()
    {
        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $result = $queryBuilder->count();

        $this->assertSame('SELECT COUNT(*) FROM table_name', $result['sql']);
    }

    public function testEmptyWhere()
    {
        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $result = $queryBuilder->toSql('SELECT *');

        $expectedSql = 'SELECT * FROM table_name';

        $this->assertSame($expectedSql, $result['sql']);
    }

    public function testEmptyGroupBy()
    {
        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $this->expectException(\InvalidArgumentException::class);
        $queryBuilder->groupBy('')->toSql('SELECT *');


    }

    public function testInvalidWhereRaw()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $queryBuilder->whereRaw('column1 = ?', [1, 2, 3])->toSql('SELECT *');
    }

    public function testNoAnnotations()
    {
        $this->expectException(\TypeError::class);
        $queryBuilder = new QueryBuilder();


        $queryBuilder->all();
    }


    public function testNullLimit()
    {
        $queryBuilder = new QueryBuilder("table_name", "connexion_name");
        $result = $queryBuilder->all(null);

        $this->assertSame('SELECT * FROM table_name', $result['sql']);
    }
}
