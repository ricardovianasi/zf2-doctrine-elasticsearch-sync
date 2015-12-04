<?php
namespace Zf2DoctrineElasticsearchSyncTest\Option\Indexing;

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
        $options = new Option\Indexing\Field();
        $this->assertEmpty($options->getCallable());
    }

    /**
     * @author Fabian Köstring
     */
    public function testIndexingSetWithConfig()
    {
        $options = new Option\Indexing\Field(
            [
                'callable' => 'getTest'
            ]
        );
        $this->assertEquals('getTest', $options->getCallable());
    }
}