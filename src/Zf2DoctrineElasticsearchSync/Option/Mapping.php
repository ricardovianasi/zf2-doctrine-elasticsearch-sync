<?php
namespace Zf2DoctrineElasticsearchSync\Option;

use Zend\Stdlib\AbstractOptions;

class Mapping extends AbstractOptions
{
    /** @var  String $type */
    private $type;

    /** @var array $parameters */
    private $parameters;

    /**
     * Getter für Attribut parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return Mapping
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Getter für Attribut type
     *
     * @return String
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param String $type
     *
     * @return Mapping
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}