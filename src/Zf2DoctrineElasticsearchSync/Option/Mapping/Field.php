<?php
namespace Zf2DoctrineElasticsearchSync\Option\Mapping;

use Zend\Stdlib\AbstractOptions;

/**
 * Class Field
 *
 * @package Zf2DoctrineElasticsearchSync\Option\Mapping
 * @author  Fabian Köstring
 */
class Field extends AbstractOptions
{
    /** @var  String $type */
    protected $type = "";

    /** @var array $parameters */
    private $parameters = [];

    /**
     * Getter für Attribut parameters
     *
     * @return array
     */
    public function getParameters() :array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return Mapping
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Getter für Attribut type
     *
     * @return String
     */
    public function getType() :string
    {
        return $this->type;
    }

    /**
     * @param String $type
     *
     * @return Mapping
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }
}