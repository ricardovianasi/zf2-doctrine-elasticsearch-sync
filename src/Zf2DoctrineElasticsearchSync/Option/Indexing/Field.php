<?php
namespace Zf2DoctrineElasticsearchSync\Option\Indexing;

use Zend\Stdlib\AbstractOptions;

/**
 * Class Field
 *
 * @package Zf2DoctrineElasticsearchSync\Option\Indexing
 * @author  Fabian Köstring
 */
class Field extends AbstractOptions
{
    /** @var  String */
    private $callable;

    /**
     * Getter für Attribut callable
     *
     * @return String
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * @param String $callable
     *
     * @return Indexing
     */
    public function setCallable($callable)
    {
        $this->callable = $callable;
        return $this;
    }

    public function getElasticsearchConfig($entity)
    {
        $callable = $this->getCallable();
        return $entity->$callable();
    }
}