<?php
namespace Zf2DoctrineElasticsearchSyncTest\Option;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager;
use Zf2DoctrineElasticsearchSync\Option;
use Zf2DoctrineElasticsearchSyncTest\Entity;

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
    public function testCreateEmptyServiceWithoutConfig()
    {
        $config = [];
        $serviceManager = $this->getMock(ServiceManager\ServiceLocatorInterface::class);
        $serviceManager
            ->expects($this->once())
            ->method('get')
            ->with('Config')
            ->will($this->returnValue($config));

        $syncFactory = new Option\SyncFactory();
        $syncService = $syncFactory->createService($serviceManager);
        $this->assertInstanceOf(Option\Sync::class, $syncService);
        $this->assertNull($syncService->getEntities());
    }

    /**
     * @author Fabian Köstring
     */
    public function testCreateServiceWithConfig()
    {
        $config = [
            'zf2-doctrine-elasticsearch-sync' => [
                'entities' => [
                    Entity\Test1::class => [],
                    Entity\Test2::class => []
                ]
            ]
        ];
        $serviceManager = $this->getMock(ServiceManager\ServiceLocatorInterface::class);
        $serviceManager
            ->expects($this->once())
            ->method('get')
            ->with('Config')
            ->will($this->returnValue($config));

        $syncFactory = new Option\SyncFactory();
        $syncService = $syncFactory->createService($serviceManager);
        $this->assertInstanceOf(Option\Sync::class, $syncService);
        foreach ($syncService->getEntities() as $entity) {
            $this->assertInstanceOf(Option\Entity::class, $entity);
        }
    }
}