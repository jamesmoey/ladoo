<?php

namespace Ladoo\GeneralLedgeBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouteCollection;
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

    protected function setUpRoute() {
        /** @var RouteCollection $bundleRouteCollection */
        $bundleRouteCollection = $this->client->getContainer()
            ->get('routing.loader')
            ->import('@LadooGeneralLedgeBundle/Resources/config/routing.yml');
        $bundleRouteCollection->addPrefix('/test');
        $this->client->getContainer()->get("router")
            ->getRouteCollection()
            ->addCollection($bundleRouteCollection);
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
}