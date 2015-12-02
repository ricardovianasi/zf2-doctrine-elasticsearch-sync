<?php
namespace Zf2DoctrineElasticsearchSyncTest\Listener;

use PHPUnit_Framework_TestCase;
use Zend\EventManager\EventManager;
use Zf2DoctrineElasticsearchSync\Listener;
use Zf2DoctrineElasticsearchSync\Option;
use Zf2DoctrineElasticsearchSyncTest\Entity;
use Zend\Config;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Elasticsearch;

/**
 * Class SyncTest
 *
 * @package Zf2DoctrineElasticsearchSyncTest\Listener
 * @author  Fabian Köstring
 */
class SyncTest extends PHPUnit_Framework_TestCase
{

    /**
     * @author Fabian Köstring
     */
    public function testOnFlushWithNoSyncedMatch()
    {
        $elasticsearchClient = $this->getMock(Elasticsearch\Client::class, [], [], '', false);
        $sync = new Listener\Sync(new Option\Sync(), $elasticsearchClient);

        $unitOfWork = $this->getMock(
            UnitOfWork::class,
            array('getScheduledEntityInsertions', 'getScheduledEntityDeletions', 'getScheduledEntityUpdates'), array(), '', false
        );
        $unitOfWork
            ->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will(
                $this->returnValue(
                    [
                        new Entity\Test1()
                    ]
                )
            );
        $unitOfWork
            ->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will(
                $this->returnValue(
                    [
                        new Entity\Test2()
                    ]
                )
            );
        $unitOfWork
            ->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will(
                $this->returnValue(
                    [
                        new Entity\Test3()
                    ]
                )
            );

        $entityManager = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            array('getUnitOfWork'), array(), '', false
        );
        $entityManager
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($unitOfWork));

        $onFlushEventArgs = new OnFlushEventArgs($entityManager);
        $sync->onFlush($onFlushEventArgs);
    }

    /**
     * @author Fabian Köstring
     */
    public function testPostFlush()
    {
        $this->assertTrue(true);
    }
}