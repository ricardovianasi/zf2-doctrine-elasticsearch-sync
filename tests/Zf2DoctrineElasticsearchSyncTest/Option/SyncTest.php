<?php
namespace Zf2DoctrineElasticsearchSyncTest\Option;

use PHPUnit_Framework_TestCase;
use Zf2DoctrineElasticsearchSync\Option;
use Zf2DoctrineElasticsearchSyncTest\Entity;

class SyncTest extends PHPUnit_Framework_TestCase
{
    /**
     * @author Fabian Köstring
     */
    public function testEntitiesNullWithoutConfig()
    {
        $options = new Option\Sync();
        $this->assertNull($options->getEntities());
    }

    /**
     * @author Fabian Köstring
     */
    public function testEntitiesSetWithConfig()
    {
        $options = new Option\Sync(
            [
                'entities' => [
                    Entity\Test1::class => [],
                    Entity\Test2::class => [],
                    Entity\Test3::class => []
                ]
            ]
        );
        $this->assertNotEmpty($options->getEntities());
        foreach ($options->getEntities() as $entity) {
            $this->assertInstanceOf(Option\Entity::class, $entity);
        }
    }

    /**
     * @author Fabian Köstring
     */
    public function testHasEntity()
    {
        $options = new Option\Sync(
            [
                'entities' => [
                    Entity\Test1::class => [],
                    Entity\Test2::class => []
                ]
            ]
        );
        $this->assertFalse($options->hasEntity(Entity\Test3::class));
        $this->assertTrue($options->hasEntity(Entity\Test2::class));
        $this->assertTrue($options->hasEntity(Entity\Test1::class));
    }

    /**
     * @author Fabian Köstring
     */
    public function testGetEntity()
    {
        $options = new Option\Sync(
            [
                'entities' => [
                    Entity\Test1::class => [],
                    Entity\Test2::class => []
                ]
            ]
        );
        $this->assertNull($options->getEntity(Entity\Test3::class));
        $this->assertInstanceOf(Option\Entity::class, $options->getEntity(Entity\Test2::class));
        $this->assertInstanceOf(Option\Entity::class, $options->getEntity(Entity\Test1::class));
    }
}