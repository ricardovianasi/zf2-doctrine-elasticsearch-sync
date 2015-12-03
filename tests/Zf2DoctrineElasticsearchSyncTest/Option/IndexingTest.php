<?php
namespace Zf2DoctrineElasticsearchSyncTest\Option;

use PHPUnit_Framework_TestCase;
use Zf2DoctrineElasticsearchSync\Option;
use Zf2DoctrineElasticsearchSyncTest\Entity;

class IndexingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @author Fabian Köstring
     */
    public function testNullWithoutConfig()
    {
        $options = new Option\Indexing();
        $this->assertNull($options->getAttribute());
    }

    /**
     * @author Fabian Köstring
     */
    public function testIndexingSetWithConfig()
    {
        $options = new Option\Indexing(
            [
                'attribute' => 'test'
            ]
        );
        $this->assertEquals('test', $options->getAttribute());
    }
}