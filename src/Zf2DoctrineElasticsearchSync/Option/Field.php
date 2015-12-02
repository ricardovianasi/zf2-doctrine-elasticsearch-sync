<?php
namespace Zf2DoctrineElasticsearchSync\Option;

use Zend\Stdlib\AbstractOptions;

class Field extends AbstractOptions
{
    /** @var Mapping */
    private $mapping;

    /** @var Indexing */
    private $indexing;

    /**
     * Getter für Attribut mapping
     *
     * @return Mapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param Mapping $mapping
     *
     * @return Field
     */
    public function setMapping($mapping)
    {
        $this->mapping = new Mapping($mapping);
        //foreach ($mapping as $mappingOptions) {
            //$this->mapping[] = new Mapping($mappingOptions);
        //}
        return $this;
    }

    /**
     * Getter für Attribut indexing
     *
     * @return Indexing
     */
    public function getIndexing()
    {
        return $this->indexing;
    }

    /**
     * @param Indexing $indexing
     *
     * @return Indexing
     */
    public function setIndexing($indexing)
    {
        $this->indexing = new Indexing($indexing);
        return $this;
    }
}