<?php
namespace Zf2DoctrineElasticsearchSync\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Common\Annotations\AnnotationReader;
use Zend\Config;
use \ReflectionClass;
use \ReflectionProperty;

/**
 * Class Sync
 *
 * @package Zf2DoctrineElasticsearchListener\Listener
 * @author  Fabian Köstring
 */
class Sync
{
    /** Config\Config $config */
    private $config;

    /** @var Elasticsearch\Client $elasticsearchClient */
    private $elasticsearchClient;

    /** @var array $inserts */
    private $inserts = [];

    /** @var array $inserts */
    private $deletions = [];

    /** @var array $inserts */
    private $updates = [];

    /**
     * OnFlush constructor.
     *
     * @param Config\Config $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->elasticsearchClient = \Elasticsearch\ClientBuilder::create()->setHosts(['127.0.0.1:9200'])->build();
    }

    /**
     * @param OnFlushEventArgs $args
     *
     * @author Fabian Köstring
     */
    public function onFlush(OnFlushEventArgs $args)
    {

        $entitiyManager = $args->getEntityManager();
        $unitOfWork = $entitiyManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $scheduledEntity) {
            if ($this->shouldSync(get_class($scheduledEntity))) {
                $this->inserts[] = $scheduledEntity;
            }
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $scheduledEntity) {
            if ($this->shouldSync(get_class($scheduledEntity))) {
                $this->deletions[] = $scheduledEntity;
            }
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $scheduledEntity) {
            if ($this->shouldSync(get_class($scheduledEntity))) {
                $this->updates[] = $scheduledEntity;
            }
        }

        $this->syncEntityUpdates();
        $this->syncEntityDeletions();
    }

    /**
     * @param PostFlushEventArgs $args
     *
     * @author Fabian Köstring
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->syncEntityInsertions();
    }


    /**
     * @param Config\Config $config
     *
     * @return bool
     * @author Fabian Köstring
     */
    private function elasticsearchTypeExists(Config\Config $config)
    {
        $params = [
            'index' => $config->get('index'),
            'type'  => $config->get('type')
        ];
        if ($this->elasticsearchClient->indices()->existsType($params)) {
            return true;
        }

        return false;
    }

    /**
     * @param string        $class
     * @param Config\Config $config
     *
     * @author Fabian Köstring
     */
    private function elasticsearchCreateType($class, Config\Config $config)
    {
        $properties = [];
        foreach ($config->get('mapping') as $elasticsearchProperty => $classAttribute) {
            $propertyColumnAnnotation = $this->getPropertyColumnAnnotation($class, $classAttribute);
            $properties[$elasticsearchProperty] = [
                'type' => $propertyColumnAnnotation->type
            ];
        }

        $params = [
            'index' => $config->get('index'),
            'body'  => [
                'mappings' => [
                    $config->get('type') => [
                        '_source'    => [
                            'enabled' => true,
                        ],
                        'properties' => $properties
                    ]
                ]
            ]
        ];
        $this->elasticsearchClient->indices()->create($params);
    }

    /**
     * @param Config\Config $config
     * @param object        $entity
     *
     * @author Fabian Köstring
     */
    private function insertEntity($config, $entity)
    {
        $id = uniqid();
        $body = [];
        foreach ($config->get('mapping') as $elasticsearchProperty => $classAttribute) {

            if (method_exists($entity, $method = ('get' . ucfirst($classAttribute)))) {
                if ($elasticsearchProperty == 'id') {
                    $id = $entity->$method();
                }
                $body[$elasticsearchProperty] = $entity->$method();
            } else {
                throw new Exception('Can\'t get property ' . $name);
            }
        }

        $params = [
            'index' => $config->get('index'),
            'type'  => $config->get('type'),
            'id'    => $id,
            'body'  => $body
        ];

        // Document will be indexed to my_index/my_type/my_id
        $this->elasticsearchClient->index($params);
    }

    /**
     * @param Config\Config $config
     * @param object        $entity
     *
     * @author Fabian Köstring
     */
    private function deleteEntity($config, $entity)
    {
        $classAttribute = $config->get('mapping')->get('id');
        if (method_exists($entity, $method = ('get' . ucfirst($classAttribute)))) {
            $id = $entity->$method();
        }

        $params = [
            'index' => $config->get('index'),
            'type'  => $config->get('type'),
            'id'    => $id
        ];

        $this->elasticsearchClient->delete($params);
    }

    /**
     * @param $class
     * @param $property
     *
     * @return \Doctrine\ORM\Mapping\Column
     * @author Fabian Köstring
     */
    private function getPropertyColumnAnnotation($class, $property)
    {
        $annotationReader = new AnnotationReader();
        $reflectionProperty = new ReflectionProperty($class, $property);
        return $annotationReader->getPropertyAnnotation($reflectionProperty, 'Doctrine\ORM\Mapping\Column');
    }

    /**
     * @throws Exception
     * @author Fabian Köstring
     */
    private function syncEntityInsertions()
    {
        foreach ($this->inserts as $scheduledEntity) {
            $scheduledEntityConfig = $this->config->get(get_class($scheduledEntity));
            if (!$this->elasticsearchTypeExists($scheduledEntityConfig)) {
                $this->elasticsearchCreateType(get_class($scheduledEntity), $scheduledEntityConfig);
            }
            $this->insertEntity($scheduledEntityConfig, $scheduledEntity);
        }
    }

    /**
     * @todo   - Fallback, was passiert wenn Dokumenet nicht existiert?
     * @throws Exception
     * @author Fabian Köstring
     */
    private function syncEntityUpdates()
    {
        foreach ($this->updates as $scheduledEntity) {
            $scheduledEntityConfig = $this->config->get(get_class($scheduledEntity));
            if (!$this->elasticsearchTypeExists($scheduledEntityConfig)) {
                $this->elasticsearchCreateType(get_class($scheduledEntity), $scheduledEntityConfig);
            }
            $this->insertEntity($scheduledEntityConfig, $scheduledEntity);
        }
    }

    /**
     * @author Fabian Köstring
     */
    private function syncEntityDeletions()
    {
        foreach ($this->deletions as $scheduledEntity) {
            $scheduledEntityConfig = $this->config->get(get_class($scheduledEntity));
            if (!$this->elasticsearchTypeExists($scheduledEntityConfig)) {
                $this->elasticsearchCreateType(get_class($scheduledEntity), $scheduledEntityConfig);
            }
            $this->deleteEntity($scheduledEntityConfig, $scheduledEntity);
        }
    }

    /**
     * @param string $key
     *
     * @return bool
     * @author Fabian Köstring
     */
    private function shouldSync($key)
    {
        if ($this->config->offsetExists($key)) {
            return true;
        }
        return false;
    }
}
