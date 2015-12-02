<?php
namespace Zf2DoctrineElasticsearchSync\Option;

use Zend\Stdlib\AbstractOptions;

/**
 * Class Sync
 *
 * @package Zf2DoctrineElasticsearchSync\Option
 * @author  Fabian Köstring
 */
class Sync extends AbstractOptions
{
    /** @var [] $entities */
    private $entities;

    /**
     * Getter für Attribut entities
     *
     * @return []
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param array $entities
     *
     * @return []
     */
    public function setEntities($entities)
    {
        foreach ($entities as $entityName => $entityOptions) {
            $this->entities[$entityName] = new Entity($entityOptions);
        }
        return $this;
    }

    /**
     * @param $entity
     *
     * @return bool
     * @author Fabian Köstring
     */
    public function hasEntity($entity)
    {
        if ($this->getEntities() && array_key_exists($entity, $this->getEntities())) {
            return true;
        }
        return false;
    }

    /**
     * @param $entity
     *
     * @return null
     * @author Fabian Köstring
     */
    public function getEntity($entity)
    {
        if ($this->hasEntity($entity)) {
            return $this->getEntities()[$entity];
        }
        return null;
    }
}