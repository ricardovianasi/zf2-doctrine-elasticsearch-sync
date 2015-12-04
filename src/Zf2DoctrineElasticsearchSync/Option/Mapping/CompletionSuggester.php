<?php
namespace Zf2DoctrineElasticsearchSync\Option\Mapping;

/**
 * Class CompletionSuggester
 *
 * @package Zf2DoctrineElasticsearchSync\Option\Mapping
 * @author  Fabian Köstring
 */
class CompletionSuggester extends Field
{
    /** @var string */
    protected $type = 'completion';
}