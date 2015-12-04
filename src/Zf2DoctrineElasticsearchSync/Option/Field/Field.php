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
     * Field constructor.
     *
     * @param array|null|\Traversable $options
     */
    public function __construct($options)
    {
        $this->mapping = new Option\Mapping\Field();
        $this->indexing = new Option\Indexing\Field();
    }

    /**
     * Getter für Attribut mapping
     *
     * @return Option\Mapping\Field
     */
    public function getMapping() :Option\Mapping\Field
    {
        return $this->mapping;
    }

    /**
     * @param Option\Mapping\Field $mapping
     *
     * @return Field
     */
    public function setMapping(Option\Mapping\Field $mapping)
    {
        $this->mapping = new Option\Mapping\Field($mapping);
        return $this;
    }

    /**
     * Getter für Attribut indexing
     *
     * @return Option\Indexing\Field
     */
    public function getIndexing() :Option\Indexing\Field
    {
        return $this->indexing;
    }

    /**
     * @param Indexing $indexing
     *
     * @return Field
     */
    public function setIndexing(Option\Indexing\Field $indexing)
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