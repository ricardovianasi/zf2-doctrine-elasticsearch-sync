<?php
namespace Zf2DoctrineElasticsearchSync\Option\Field;
use Zf2DoctrineElasticsearchSync\Option;

/**
 * Class CompletionSuggester
 *
 * @package Zf2DoctrineElasticsearchSync\Option\Field
 * @author  Fabian Köstring
 */
class CompletionSuggester extends Field
{
    /**
     * @inheritDoc
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = new Option\Mapping\CompletionSuggester($mapping);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setIndexing(array $indexing)
    {
        $this->indexing = new Option\Indexing\CompletionSuggester($indexing);
        return $this;
    }
}