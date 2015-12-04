<?php
namespace Zf2DoctrineElasticsearchSync\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Common\Annotations\AnnotationReader;
use Zend\Config;
use \ReflectionClass;
use \ReflectionProperty;
use Zf2DoctrineElasticsearchSync\Exception;
use Zf2DoctrineElasticsearchSync\Option;
use Elasticsearch;

/**
 * Class Sync
 *
 * @package Zf2DoctrineElasticsearchListener\Listener
 * @author  Fabian Köstring
 */
class Sync
{
    /** Option\Sync $options */
    private $options;

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
     * @param Option\Sync $options
     */
    public function __construct(Option\Sync $options, Elasticsearch\Client $elasticsearchClient)
    {
        $this->options = $options;
        $this->elasticsearchClient = $elasticsearchClient;
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
            if ($this->options->hasEntity(get_class($scheduledEntity))) {
                $this->inserts[] = $scheduledEntity;
            }
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $scheduledEntity) {
            if ($this->options->hasEntity(get_class($scheduledEntity))) {
                $this->deletions[] = $scheduledEntity;
            }
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $scheduledEntity) {
            if ($this->options->hasEntity(get_class($scheduledEntity))) {
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
     * @param Option\Entity $entityOptions
     *
     * @return bool
     * @author Fabian Köstring
     */
    private function elasticsearchTypeExists(Option\Entity $entityOptions)
    {
        $params = [
            'index' => $entityOptions->getIndex(),
            'type'  => $entityOptions->getType()
        ];
        if ($this->elasticsearchClient->indices()->existsType($params)) {
            return true;
        }

        return false;
    }

    /**
     * @param               $class
     * @param Option\Entity $entityOptions
     *
     * @author Fabian Köstring
     * @todo   throw Exception if $properties is empty. No mapping processed.
     */
    private function elasticsearchCreateType($class, Option\Entity $entityOptions)
    {
        $properties = [];
        foreach ($entityOptions->getFields() as $property => $entityOption) {
            if ($property != 'id' && $entityOption->getMapping()) {
                $mapping = $entityOption->getMapping();
                $properties[$property] = array_merge(['type' => $mapping->getType()], $mapping->getParameters());
            }
        }

        $params = [
            'index' => $entityOptions->getIndex(),
            'body'  => [
                'mappings' => [
                    $entityOptions->getType() => [
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

    private function insertEntity($entity, Option\Entity $entityOptions)
    {
        $id = null;
        $body = [];
        foreach ($entityOptions->getFields() as $property => $entityOption) {
            if ($entityOption->getIndexing()) {
                $indexing = $entityOption->getIndexing();
                $body[$property] = $indexing->getElasticsearchConfig($entity);
            }
        }

        $params = [
            'index' => $entityOptions->getIndex(),
            'type'  => $entityOptions->getType(),
            'body'  => $body
        ];

        if (!is_null($id)) {
            $params['id'] = $id;
        }

        $this->elasticsearchClient->index($params);
    }

    /**
     * @param               $entity
     * @param Option\Entity $entityOptions
     *
     * @author Fabian Köstring
     * @todo   throw exception if id could not found
     */
    private function deleteEntity($entity, Option\Entity $entityOptions)
    {
        $id = $entityOptions->getField('id');
        $indexing = $id->getIndexing();

        if (method_exists($entity, $method = ('get' . ucfirst($indexing->getAttribute())))) {
            $id = $entity->$method();
        }

        $params = [
            'index' => $entityOptions->getIndex(),
            'type'  => $entityOptions->getType(),
            'id'    => $id
        ];

        $this->elasticsearchClient->delete($params);
    }

    /**
     * @throws Exception
     * @author Fabian Köstring
     */
    private function syncEntityInsertions()
    {
        foreach ($this->inserts as $scheduledEntity) {
            $entityOptions = $this->options->getEntity(get_class($scheduledEntity));
            if (!$this->elasticsearchTypeExists($entityOptions)) {
                $this->elasticsearchCreateType(get_class($scheduledEntity), $entityOptions);
            }
            $this->insertEntity($scheduledEntity, $entityOptions);
        }
    }

    /**
     * @author Fabian Köstring
     * @todo   Call update not insert?
     */
    private function syncEntityUpdates()
    {
        foreach ($this->updates as $scheduledEntity) {
            $entityOptions = $this->options->getEntity(get_class($scheduledEntity));
            if ($this->elasticsearchTypeExists($entityOptions)) {
                $this->insertEntity($scheduledEntity, $entityOptions);
            }
        }
    }

    /**
     * @author Fabian Köstring
     */
    private function syncEntityDeletions()
    {
        foreach ($this->deletions as $scheduledEntity) {
            $entityOptions = $this->options->getEntity(get_class($scheduledEntity));
            if ($this->elasticsearchTypeExists($entityOptions)) {
                $this->deleteEntity($scheduledEntity, $entityOptions);
            }
        }
    }
}
