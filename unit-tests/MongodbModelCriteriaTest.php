<?php
defined('UNIT_TESTS_ROOT') || require __DIR__ . '/bootstrap.php';

require __DIR__ . '/TApplication/Application.php';

use Mongodb\Models\City;

class MongodbModelCriteriaTest extends TestCase
{
    public function setUp()
    {
        $di = new \ManaPHP\Di\FactoryDefault();

        $config = require __DIR__ . '/config.database.php';
        $di->setShared('mongodb', new ManaPHP\Mongodb($config['mongodb']));
    }

    public function test_construct()
    {
        $document = City::createCriteria()->fetchOne();
        $this->assertEquals(['_id', 'city_id', 'city', 'country_id', 'last_update'], array_keys($document));

        $document = City::createCriteria(['city_id', 'city'])->fetchOne();
        $this->assertEquals(['_id', 'city_id', 'city'], array_keys($document));
    }

    public function test_distinctField()
    {
        $this->assertCount(600, City::createCriteria()->distinctField('city_id'));
        $this->assertCount(3, City::createCriteria()->where('country_id', 2)->distinctField('city_id'));
    }

    public function test_select()
    {
        $documents = City::createCriteria()->limit(1)->fetchAll();
        $this->assertCount(1, $documents);
        $this->assertEquals(['_id', 'city_id', 'city', 'country_id', 'last_update'], array_keys($documents[0]));

        $documents = City::createCriteria()->select('_id, city_id, city')->limit(1)->fetchAll();
        $this->assertCount(1, $documents);
        $this->assertEquals(['_id', 'city_id', 'city'], array_keys($documents[0]));

        $documents = City::createCriteria()->select(['_id', 'city_id', 'city'])->limit(1)->fetchAll();
        $this->assertCount(1, $documents);
        $this->assertEquals(['_id', 'city_id', 'city'], array_keys($documents[0]));
    }

    public function test_aggregate()
    {
        $documents = City::createCriteria()->aggregate(['max' => 'MAX(city_id)'])->fetchOne();
        $this->assertEquals(600, $documents['max']);

        $documents = City::createCriteria()->aggregate(['min' => 'MIN(city_id)'])->fetchOne();
        $this->assertEquals(1, $documents['min']);

        $documents = City::createCriteria()->aggregate(['avg' => 'AVG(city_id)'])->fetchOne();
        $this->assertEquals(300.5, $documents['avg']);

        $documents = City::createCriteria()->aggregate(['sum' => 'SUM(city_id)'])->fetchOne();
        $this->assertEquals(180300, $documents['sum']);

        $documents = City::createCriteria()->aggregate(['avg' => 'AVG(city_id)'])->fetchOne();
        $this->assertEquals(300.5, $documents['avg']);

        $documents = City::createCriteria()->aggregate(['sum' => 'SUM(city_id)', 'avg' => 'AVG(city_id)'])->fetchOne();
        $this->assertEquals(180300, $documents['sum']);
        $this->assertEquals(300.5, $documents['avg']);

        $documents = City::createCriteria()->aggregate(['max' => 'MAX(city_id+country_id)'])->fetchOne();
        $this->assertEquals(691, $documents['max']);

        $documents = City::createCriteria()->aggregate(['max' => 'MAX(city_id + country_id)'])->fetchOne();
        $this->assertEquals(691, $documents['max']);

        $documents = City::createCriteria()->aggregate(['max' => ['$max' => ['$add' => ['$city_id', '$country_id']]]])->fetchOne();
        $this->assertEquals(691, $documents['max']);

        $documents = City::createCriteria()->aggregate(['sum' => 'sum(city_id*country_id)'])->fetchOne();
        $this->assertEquals(10003864, $documents['sum']);

        $documents = City::createCriteria()->aggregate(['sum' => 'SUM(city_id*2.5)'])->fetchOne();
        $this->assertEquals(450750, $documents['sum']);
    }

    public function test_where()
    {
        $document = City::createCriteria()->where('city_id', 2)->fetchOne();
        $this->assertEquals(2, $document['city_id']);

        $document = City::createCriteria()->where(['city_id' => 2])->fetchOne();
        $this->assertEquals(2, $document['city_id']);

        $documents = City::createCriteria()->where('city_id', -2)->fetchAll();
        $this->assertEmpty($documents);

        $documents = City::createCriteria()->where('city_id=', 10)->fetchAll();
        $this->assertCount(1, $documents);
        $this->assertEquals(10, $documents[0]['city_id']);

        $documents = City::createCriteria()->where('city_id>', 10)->fetchAll();
        $this->assertCount(590, $documents);
        $this->assertEquals(11, $documents[0]['city_id']);

        $documents = City::createCriteria()->where('city_id>=', 10)->fetchAll();
        $this->assertCount(591, $documents);
        $this->assertEquals(10, $documents[0]['city_id']);

        $documents = City::createCriteria()->where('city_id<', 10)->fetchAll();
        $this->assertCount(9, $documents);
        $this->assertEquals(1, $documents[0]['city_id']);

        $documents = City::createCriteria()->where('city_id<=', 10)->fetchAll();
        $this->assertCount(10, $documents);
        $this->assertEquals(1, $documents[0]['city_id']);

        $documents = City::createCriteria()->where('city_id!=', 10)->fetchAll();
        $this->assertCount(599, $documents);
        $this->assertEquals(1, $documents[0]['city_id']);

        $documents = City::createCriteria()->where('city_id<>', 10)->fetchAll();
        $this->assertCount(599, $documents);
        $this->assertEquals(1, $documents[0]['city_id']);

        $documents = City::createCriteria()->where('city_id', ['$ne' => 10])->fetchAll();
        $this->assertCount(599, $documents);
        $this->assertEquals(1, $documents[0]['city_id']);
    }

    public function test_betweenWhere()
    {
        $documents = City::createCriteria()->betweenWhere('city_id', 2, 3)->fetchAll();
        $this->assertCount(1, $documents);

        $documents = City::createCriteria()->betweenWhere('city_id', 2, 2)->fetchAll();
        $this->assertCount(0, $documents);
    }

    public function test_notBetweenWhere()
    {
        $documents = City::createCriteria()->notBetweenWhere('city_id', 100, 600)->fetchAll();
        $this->assertCount(100, $documents);

        $documents = City::createCriteria()->notBetweenWhere('city_id', 50, 200)->notBetweenWhere('city_id', 200, 600)->fetchAll();
        $this->assertCount(50, $documents);
    }

    public function test_inWhere()
    {
        $documents = City::createCriteria()->inWhere('city_id', [1, 2, 3, 4])->fetchAll();
        $this->assertCount(4, $documents);

        $documents = City::createCriteria()->inWhere('city_id', [1, 2, 3, 4])->inWhere('city_id', [2, 4])->fetchAll();
        $this->assertCount(2, $documents);

        $documents = City::createCriteria()->inWhere('city_id', [])->fetchAll();
        $this->assertCount(0, $documents);
    }

    public function test_notInWhere()
    {
        $documents = City::createCriteria()->notInWhere('city_id', [1, 2, 3, 4])->fetchAll();
        $this->assertCount(596, $documents);

        $documents = City::createCriteria()->notInWhere('city_id', [])->fetchAll();
        $this->assertCount(600, $documents);
    }

    public function test_orderBy()
    {
        $documents = City::createCriteria()->orderBy('city_id')->limit(10, 100)->fetchAll();
        $this->assertEquals(101, $documents[0]['city_id']);

        $documents = City::createCriteria()->orderBy('city_id asc')->limit(10, 100)->fetchAll();
        $this->assertEquals(101, $documents[0]['city_id']);

        $documents = City::createCriteria()->orderBy('city_id desc')->limit(10, 100)->fetchAll();
        $this->assertEquals(500, $documents[0]['city_id']);

        $documents = City::createCriteria()->orderBy('country_id desc, city_id desc')->limit(10, 100)->fetchAll();
        $this->assertEquals(526, $documents[0]['city_id']);

        $documents = City::createCriteria()->orderBy(['city_id' => SORT_ASC])->limit(10, 100)->fetchAll();
        $this->assertEquals(101, $documents[0]['city_id']);

        $documents = City::createCriteria()->orderBy(['city_id' => SORT_DESC])->limit(10, 100)->fetchAll();
        $this->assertEquals(500, $documents[0]['city_id']);

        $documents = City::createCriteria()->orderBy(['city_id' => 'desc'])->limit(10, 100)->fetchAll();
        $this->assertEquals(500, $documents[0]['city_id']);
    }

    public function test_limit()
    {
        $documents = City::createCriteria()->limit(10)->fetchAll();
        $this->assertCount(10, $documents);
    }

    public function test_page()
    {
        $documents = City::createCriteria()->page(10, 2)->fetchAll();
        $this->assertCount(10, $documents);
        $this->assertEquals(11, $documents[0]['city_id']);
    }

//    public function test_groupBy(){
//        $documents=City::createCriteria()->groupBy('country_id')->execute();
//        $this->assertEquals([],$documents);
//    }

    public function test_groupBy()
    {
        $documents = City::createCriteria()->aggregate(['sum' => 'SUM(city_id)'])->groupBy('country_id')->fetchAll();
        $this->assertCount(109, $documents);

        $documents = City::createCriteria()->aggregate(['sum' => 'SUM(city_id)'])->groupBy('country_id, city_id')->fetchAll();
        $this->assertCount(600, $documents);

        $documents = City::createCriteria()->where(['country_id' => 24])->aggregate(['sum' => 'SUM(city_id)'])->groupBy('country_id')->fetchAll();
        $this->assertCount(1, $documents);

        $documents = City::createCriteria()->aggregate(['count' => 'COUNT(*)'])->groupBy(['city' => ['$substr' => ['$city', 0, 1]]])->orderBy('count')->fetchAll();
        $this->assertCount(30, $documents);

        $documents = City::createCriteria()->aggregate(['count' => 'COUNT(*)'])->groupBy('substr(city, 1, 1)')->orderBy('count')->fetchAll();
        $this->assertCount(30, $documents);

        $documents = City::createCriteria()->aggregate(['count' => 'COUNT(*)'])->groupBy('substr(city, 1, 1)')->orderBy('count')->indexBy('city')->fetchAll();
        $this->assertCount(30, $documents);
        $this->assertArrayHasKey('s', $documents);
    }

    public function test_indexBy()
    {
        $this->assertArrayHasKey('s', City::createCriteria()->aggregate(['count' => 'COUNT(*)'])->groupBy('substr(city, 1, 1)')->indexBy('city')->fetchAll());
        $this->assertArrayHasKey(600, City::createCriteria()->indexBy('city_id')->fetchAll());
    }
}