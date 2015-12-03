<?php
namespace Zf2DoctrineElasticsearchSyncTest\Service;

use PHPUnit_Framework_TestCase;
use Zf2DoctrineElasticsearchSync\Service;
use Zf2DoctrineElasticsearchSync\Listener;
use Zf2DoctrineElasticsearchSync\Option;
use Zend\Http\Response;
use Zend\Http\Request;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\Mvc\MvcEvent;
use Doctrine\ORM;
use Doctrine\Common;
use Elasticsearch\ClientBuilder;
use Elasticsearch;

class ElasticsearchFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $service = new Service\ElasticsearchFactory();
        $this->assertInstanceOf(Elasticsearch\Client::class, $service->createService($serviceManager));
    }
}