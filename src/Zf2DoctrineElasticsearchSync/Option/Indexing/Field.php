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

        /*
        if (is_null($this->getCallable()) && is_null($this->getAttribute())) {
            die('Exception 1');
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s: Property "%s" in entity of type "%s" must have one indexing option. Please define a callable OR an attribute."',
                    __METHOD__,
                    $property,
                    get_class($entity)
                )
            );
        }

        if ($this->getCallable() && $this->getAttribute()) {
            die('Exception 2');
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s: Property "%s" in entity of type "%s" should have only one indexing option. Please define a callable OR an attribute."',
                    __METHOD__,
                    $property,
                    get_class($entity)
                )
            );
        }

        if (!is_null($this->getCallable())) {
            if (method_exists($entity, $method = ($indexing->getCallable()))) {
                if ($property == 'id') {
                    $id = $entity->$method();
                } else {
                    $body[$property] = $entity->$method();
                }
            } else {
                throw new Exception\InvalidArgumentException(
                    sprintf(
                        '%s: Defined callable in entity "%s" for property "%s" could not be found."',
                        __METHOD__,
                        get_class($entity),
                        $property
                    )
                );
            }
        }

        if (!is_null($indexing->getAttribute())) {
            if (method_exists($entity, $method = ('get' . ucfirst($indexing->getAttribute())))) {
                if ($property == 'id') {
                    $id = $entity->$method();
                } else {
                    $body[$property] = $entity->$method();
                }
            } else {
                throw new Exception\InvalidArgumentException(
                    sprintf(
                        '%s: Defined attribute in entity "%s" for property "%s" could not be found."',
                        __METHOD__,
                        get_class($entity),
                        $property
                    )
                );
            }
        }
        */
    }
}