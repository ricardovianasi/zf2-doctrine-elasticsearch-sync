<?php
namespace Zf2DoctrineElasticsearchSync\Option\Field;

use Zend\Stdlib\AbstractOptions;
use Zf2DoctrineElasticsearchSync\Option;

class Field extends AbstractOptions
{
    /** @var Type\Completion */
    private $type;

    /** @var Option\Mapping\Field */
    protected $mapping;

    /** @var Option\Indexing\Field */
    protected $indexing;


    /**
     * Getter für Attribut mapping
     *
     * @return Option\Mapping\Field
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param array $mapping
     *
     * @return Field
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = new Option\Mapping\Field($mapping);
        return $this;
    }

    /**
     * Getter für Attribut indexing
     *
     * @return Option\Indexing\Field
     */
    public function getIndexing()
    {
        return $this->indexing;
    }

    /**
     * @param array $indexing
     *
     * @return Field
     */
    public function setIndexing(array $indexing)
    {
        $this->indexing = new Option\Indexing\Field($indexing);
        return $this;
    }

    /**
     * Getter für Attribut type
     *
     * @return Type\Completion
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Type\Completion $type
     *
     * @return Field
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}