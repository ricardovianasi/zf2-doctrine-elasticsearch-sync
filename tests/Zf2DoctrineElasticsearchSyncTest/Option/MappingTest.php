<?php
namespace Zf2DoctrineElasticsearchSyncTest\Option;

use PHPUnit_Framework_TestCase;
use Zf2DoctrineElasticsearchSync\Option;
use Zf2DoctrineElasticsearchSyncTest\Entity;

class MappingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @author Fabian Köstring
     */
    public function testNullWithoutConfig()
    {
        $options = new Option\Mapping();
        $this->assertNull($options->getType());
        $this->assertNull($options->getParameters());
    }

    /**
     * @author Fabian Köstring
     */
    public function testEntitiesSetWithConfig()
    {
        $options = new Option\Mapping(
            [
                'type'       => 'string',
                'parameters' => [
                    'analyzer' => 'default',
                    'boost'    => '1.0',
                ]
            ]
        );
        $this->assertEquals('string', $options->getType());
        $this->assertArrayHasKey('analyzer', $options->getParameters());
        $this->assertArrayHasKey('boost', $options->getParameters());
    }
}