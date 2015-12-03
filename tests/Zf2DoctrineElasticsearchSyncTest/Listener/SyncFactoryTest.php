<?php
namespace Zf2DoctrineElasticsearchSyncTest\Listener;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager;
use Zf2DoctrineElasticsearchSync\Option;
use Zf2DoctrineElasticsearchSync\Listener;
use Zf2DoctrineElasticsearchSyncTest\Entity;
use Elasticsearch;

/**
 * Class SyncFactoryTest
 *
 * @package Zf2DoctrineElasticsearchSyncTest\Service
 * @author  Fabian Köstring
 */
class SyncFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @author Fabian Köstring
     */
    public function testCreateService()
    {
        $elasticsearchClient = $this->getMock(Elasticsearch\Client::class, [], [], '', false);
        $serviceManager = $this->getMock(ServiceManager\ServiceLocatorInterface::class);
        $serviceManager
            ->expects($this->at(0))
            ->method('get')
            ->with(Option\Sync::class)
            ->will($this->returnValue(new Option\Sync()));
        $serviceManager
            ->expects($this->at(1))
            ->method('get')
            ->with('zf2-doctrine-elasticsearch-service')
            ->will($this->returnValue($elasticsearchClient));

        $syncFactory = new Listener\SyncFactory();
        $syncService = $syncFactory->createService($serviceManager);
        $this->assertInstanceOf(Listener\Sync::class, $syncService);
    }
}