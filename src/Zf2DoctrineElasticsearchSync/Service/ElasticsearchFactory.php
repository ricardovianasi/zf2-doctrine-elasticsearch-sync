<?php
namespace Zf2DoctrineElasticsearchSync\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Elasticsearch
 *
 * @package Zf2DoctrineElasticsearchSync\Factory
 * @author  Fabian Köstring
 */
class ElasticsearchFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return \Elasticsearch\Client
     * @author Fabian Köstring
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return \Elasticsearch\ClientBuilder::create()->setHosts(['127.0.0.1:9200'])->build();
    }
}