<?php
namespace Zf2DoctrineElasticsearchSync;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\Loader\StandardAutoloader;
use Zend\Loader\AutoloaderFactory;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;
use Zend\Config;
use Doctrine\ORM;
use Doctrine\Common;
use Zf2DoctrineElasticsearchSync\Listener;
use Zf2DoctrineElasticsearchSync\Option;

/**
 * Class Module
 *
 * @package Zf2DoctrineElasticsearchSync
 * @author  Fabian Köstring
 */
class Module implements AutoloaderProviderInterface, ConfigProviderInterface, BootstrapListenerInterface
{
    /**
     * @return array
     * @author Fabian Köstring
     */
    public function getAutoloaderConfig()
    {
        return array(
            AutoloaderFactory::STANDARD_AUTOLOADER => array(
                StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * @return mixed
     * @author Fabian Köstring
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * @param EventInterface $event
     *
     * @author Fabian Köstring
     */
    public function onDispatch(EventInterface $event)
    {
        if ($event instanceof MvcEvent) {
            /** @var ServiceManager $serviceManager */
            $serviceManager = $event->getApplication()->getServiceManager();
            /** @var ORM\EntityManager $entityManager */
            $entityManager = $serviceManager->get(ORM\EntityManager::class);
            /** @var Common\EventManager $eventManager */
            $eventManager = $entityManager->getEventManager();
            /** @var Listener\Sync $listener */
            $listener = $serviceManager->get(Listener\Sync::class);
            $eventManager->addEventListener([ORM\Events::onFlush, ORM\Events::postFlush], $listener);
        }
    }

    /**
     * @param EventInterface $event
     *
     * @author Fabian Köstring
     */
    public function onBootstrap(EventInterface $event)
    {
        if ($event instanceof MvcEvent) {
            /** @var ServiceManager $serviceManager */
            $serviceManager = $event->getApplication()->getServiceManager();
            $config = $serviceManager->get('Config');

            /** @todo - check if if required */
            if (isset($config['zf2-doctrine-elasticsearch-sync']) && !empty($config['zf2-doctrine-elasticsearch-sync'])) {
                /** @var EventManager $eventManager */
                $eventManager = $event->getTarget()->getEventManager();
                $eventManager->attach(MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 999999);
            }
        }
    }
}
