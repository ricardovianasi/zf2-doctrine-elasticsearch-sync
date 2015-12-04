<?php
namespace Zf2DoctrineElasticsearchSyncTest\Option\Mapping;

use PHPUnit_Framework_TestCase;
use Zf2DoctrineElasticsearchSync\Option;
use Zf2DoctrineElasticsearchSyncTest\Entity;

class FieldTest extends PHPUnit_Framework_TestCase
{
    /**
     * @author Fabian Köstring
     */
    public function testNullWithoutConfig()
    {
        $options = new Option\Mapping\Field();
        $this->assertEmpty($options->getType());
        $this->assertEmpty($options->getParameters());
    }

    /**
     * @author Fabian Köstring
     */
    public function testEntitiesSetWithConfig()
    {
        $options = new Option\Mapping\Field(
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