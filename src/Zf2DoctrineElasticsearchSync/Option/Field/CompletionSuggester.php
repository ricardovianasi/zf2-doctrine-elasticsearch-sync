<?php
namespace Zf2DoctrineElasticsearchSync\Option\Field;

/**
 * Class CompletionSuggester
 *
 * @package Zf2DoctrineElasticsearchSync\Option\Field
 * @author  Fabian Köstring
 */
class CompletionSuggester extends Field
{
    /**
     * @param Mapping $mapping
     *
     * @return Field
     */
    public function setMapping($mapping)
    {
        $this->mapping = new Option\Mapping\CompletionSuggester($mapping);
        return $this;
    }

    /**
     * @param Indexing $indexing
     *
     * @return Indexing
     */
    public function setIndexing($indexing)
    {
        $this->indexing = new Option\Indexing\CompletionSuggester($indexing);
        return $this;
    }
}