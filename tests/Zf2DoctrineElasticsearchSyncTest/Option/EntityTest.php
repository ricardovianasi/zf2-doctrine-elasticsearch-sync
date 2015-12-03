<?php
namespace Zf2DoctrineElasticsearchSyncTest\Option;

use PHPUnit_Framework_TestCase;
use Zf2DoctrineElasticsearchSync\Option;
use Zf2DoctrineElasticsearchSyncTest\Entity;

class EntityTest extends PHPUnit_Framework_TestCase
{
    /**
     * @author Fabian Köstring
     */
    public function testNullWithoutConfig()
    {
        $options = new Option\Entity();
        $this->assertNull($options->getAlias());
        $this->assertNull($options->getIndex());
        $this->assertNull($options->getType());
        $this->assertNull($options->getFields());
    }

    /**
     * @author Fabian Köstring
     */
    public function testEntitiesSetWithConfig()
    {
        $options = new Option\Entity(
            [
                'index'  => 'test-index-1',
                'type'   => 'test-type-1',
                'alias'  => 'test-alias-1',
                'fields' => [
                    'test-field-1' => [
                        'mapping'  => [
                            'type' => 'string'
                        ],
                        'indexing' => [
                            'attribute' => 'id'
                        ]
                    ],
                    'test-field-2' => [
                        'mapping'  => [
                            'type' => 'string'
                        ],
                        'indexing' => [
                            'attribute' => 'id'
                        ]
                    ]
                ]
            ]
        );
        $this->assertEquals('test-alias-1', $options->getAlias());
        $this->assertEquals('test-type-1', $options->getType());
        $this->assertEquals('test-index-1', $options->getIndex());
        foreach ($options->getFields() as $field) {
            $this->assertInstanceOf(Option\Field::class, $field);
        }
    }

    /**
     * @author Fabian Köstring
     */
    public function testHasField()
    {
        $options = new Option\Entity(
            [
                'index'  => 'test-index-1',
                'type'   => 'test-type-1',
                'alias'  => 'test-alias-1',
                'fields' => [
                    'test-field-1' => [
                        'mapping'  => [
                            'type' => 'string'
                        ],
                        'indexing' => [
                            'attribute' => 'id'
                        ]
                    ],
                    'test-field-2' => [
                        'mapping'  => [
                            'type' => 'string'
                        ],
                        'indexing' => [
                            'attribute' => 'id'
                        ]
                    ]
                ]
            ]
        );
        $this->assertFalse($options->hasField('test-field-3'));
        $this->assertTrue($options->hasField('test-field-1'));
        $this->assertTrue($options->hasField('test-field-2'));
    }

    /**
     * @author Fabian Köstring
     */
    public function testGetField()
    {
        $options = new Option\Entity(
            [
                'index'  => 'test-index-1',
                'type'   => 'test-type-1',
                'alias'  => 'test-alias-1',
                'fields' => [
                    'test-field-1' => [
                        'mapping'  => [
                            'type' => 'string'
                        ],
                        'indexing' => [
                            'attribute' => 'id'
                        ]
                    ],
                    'test-field-2' => [
                        'mapping'  => [
                            'type' => 'string'
                        ],
                        'indexing' => [
                            'attribute' => 'id'
                        ]
                    ]
                ]
            ]
        );
        $this->assertNull($options->getField('test-field-3'));
        $this->assertInstanceOf(Option\Field::class, $options->getField('test-field-1'));
        $this->assertInstanceOf(Option\Field::class, $options->getField('test-field-2'));
    }
}