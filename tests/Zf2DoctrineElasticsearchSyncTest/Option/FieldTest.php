<?php
namespace Zf2DoctrineElasticsearchSyncTest\Option;

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
        $options = new Option\Field();
        $this->assertNull($options->getIndexing());
        $this->assertNull($options->getMapping());
    }

    /**
     * @author Fabian Köstring
     */
    public function testFieldSetWithConfig()
    {
        $options = new Option\Field(
            [
                'mapping'  => [
                    'type' => 'string'
                ],
                'indexing' => [
                    'attribute' => 'test'
                ]
            ]
        );
        $this->assertNotEmpty($options->getMapping());
        $this->assertNotEmpty($options->getIndexing());
    }
}