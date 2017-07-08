<?php

namespace Ladoo\GeneralLedgeBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class BaseControllerTestCase extends WebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    /**
     * @param string $serviceName
     * @param string $class
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockRepository(string $serviceName, string $class) {
        $repository = $this
            ->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->client->getContainer()->set($serviceName, $repository);
        return $repository;
    }

    public function buildEntityInstance($class, $id) {
        $obj = new $class();
        $idProperty = new \ReflectionProperty($class, 'id');
        $idProperty->setAccessible(\ReflectionProperty::IS_PUBLIC);
        $idProperty->setValue($obj, $id);
        return $obj;
    }
}