<?php
namespace Zf2DoctrineElasticsearchSync\Option;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SyncFactory
 *
 * @package Zf2DoctrineElasticsearchSync\Options
 * @author  Fabian Köstring
 */
class SyncFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return Sync
     * @author Fabian Köstring
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        if (isset($config['zf2-doctrine-elasticsearch-sync'])) {
            return new Sync($config['zf2-doctrine-elasticsearch-sync']);
        }

        return new Sync();
    }
}