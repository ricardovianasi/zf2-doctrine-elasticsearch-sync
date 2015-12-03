<?php
namespace Zf2DoctrineElasticsearchSyncTest;

use PHPUnit_Framework_TestCase;
use Zf2DoctrineElasticsearchSync\Module;
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

/**
 * Class ModuleTest
 *
 * @package Zf2DoctrineElasticsearchSyncTest
 * @author  Fabian Köstring
 */
class ModuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @author Fabian Köstring
     */
    public function testGetAutoloaderConfig()
    {
        $module = new Module();
        $this->assertInternalType('array', $module->getAutoloaderConfig());
    }

    /**
     * @author Fabian Köstring
     */
    public function testGetConfig()
    {
        $module = new Module();
        $this->assertInternalType('array', $module->getConfig());
    }

    /**
     * @author Fabian Köstring
     */
    public function testOnBootstrap()
    {
        $applicationEventManager = new EventManager();

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue(['zf2-doctrine-elasticsearch-sync' => ['asd']]));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($applicationEventManager));
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event = new MvcEvent();
        $event->setTarget($application);
        $event->setApplication($application);
        $module = new Module();
        $module->onBootstrap($event);

        $dispatchListeners = $applicationEventManager->getListeners(MvcEvent::EVENT_DISPATCH);
        foreach ($dispatchListeners as $listener) {
            $metaData = $listener->getMetadata();
            $callback = $listener->getCallback();
            $this->assertEquals('onDispatch', $callback[1]);
            $this->assertEquals(999999, $metaData['priority']);
            $this->assertTrue($callback[0] instanceof Module);
        }
    }

    /**
     * @author Fabian Köstring
     */
    public function testOnBootstrapNoConfig()
    {
        $applicationEventManager = new EventManager();

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue([]));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($applicationEventManager));
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event = new MvcEvent();
        $event->setTarget($application);
        $event->setApplication($application);
        $module = new Module();
        $module->onBootstrap($event);
        $this->assertEmpty($applicationEventManager->getListeners(MvcEvent::EVENT_DISPATCH));
        $this->assertEmpty($applicationEventManager->getListeners(ORM\Events::onFlush));
    }

    /**
     * @author Fabian Köstring
     */
    public function testOnBootstrapEmptyConfig()
    {
        $applicationEventManager = new EventManager();

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue(['zf2-doctrine-elasticsearch-sync' => []]));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($applicationEventManager));
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event = new MvcEvent();
        $event->setTarget($application);
        $event->setApplication($application);
        $module = new Module();
        $module->onBootstrap($event);
        $this->assertEmpty($applicationEventManager->getListeners(MvcEvent::EVENT_DISPATCH));
        $this->assertEmpty($applicationEventManager->getListeners(ORM\Events::onFlush));
    }

    /**
     * @author Fabian Köstring
     */
    public function testOnDispatch()
    {
        $elasticsearchClient = $this->getMock(Elasticsearch\Client::class, [], [], '', false);
        $listener = new Listener\Sync(new Option\Sync(), $elasticsearchClient);
        $eventManager = new Common\EventManager();
        $entityManager = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            array('getEventManager'), array(), '', false
        );
        $entityManager->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->at(0))
            ->method('get')
            ->with(ORM\EntityManager::class)
            ->will($this->returnValue($entityManager));
        $serviceManager
            ->expects($this->at(1))
            ->method('get')
            ->with(Listener\Sync::class)
            ->will($this->returnValue($listener));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event = new MvcEvent();
        $event->setApplication($application);

        $this->assertEmpty($eventManager->getListeners());
        $module = new Module();
        $module->onDispatch($event);
        $this->assertArrayHasKey('onFlush', $eventManager->getListeners());
        $this->assertArrayHasKey('postFlush', $eventManager->getListeners());
    }
}