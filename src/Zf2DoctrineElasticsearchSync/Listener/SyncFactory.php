<?php
namespace Zf2DoctrineElasticsearchSync\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zf2DoctrineElasticsearchSync\Option;
use Elasticsearch;

/**
 * Class SyncFactory
 *
 * @package Zf2DoctrineElasticsearchSync\Listener
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
        /** @var Option\Sync $syncOptions */
        $syncOptions = $serviceLocator->get(Option\Sync::class);

        /** @var Elasticsearch\Client $elasticsearchService */
        $elasticsearchService = $serviceLocator->get('zf2-doctrine-elasticsearch-service');

        return new Sync($syncOptions, $elasticsearchService);
    }
}