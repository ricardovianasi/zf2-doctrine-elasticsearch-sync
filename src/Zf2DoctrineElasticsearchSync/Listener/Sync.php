<?php
namespace Zf2DoctrineElasticsearchSync\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Common\Annotations\AnnotationReader;
use Zend\Config;
use \ReflectionClass;
use \ReflectionProperty;
use Zf2DoctrineElasticsearchSync\Exception;

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
     * @todo - object, nested, geo_point, geo_shape
     * @var array $mappingTypes
     */
    private $mappingTypes = ['string', 'date', 'long', 'double', 'boolean', 'ip', 'completion'];

    /**
     * OnFlush constructor.
     *
     * @param Config\Config $config
     */
    public function __construct(Config\Config $config, Elasticsearch\Client $elasticsearchClient)
    {
        $this->config = $config;
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
     * @param               $class
     * @param Config\Config $config
     *
     * @author Fabian Köstring
     */
    private function elasticsearchCreateType($class, Config\Config $config)
    {
        $properties = [];
        /**
         * @var $elasticsearchProperty String
         * @var $fieldConfig           Config\Config
         */
        foreach ($config->get('fields') as $elasticsearchProperty => $fieldConfig) {
            if ($elasticsearchProperty != 'id') {
                if ($fieldConfig->offsetExists('mapping')) {
                    $fieldMapping = $fieldConfig->offsetGet('mapping');
                    if ($fieldMapping->offsetExists('type')) {
                        $fieldType = $fieldMapping->offsetGet('type');
                        if (!is_string($fieldType) || !in_array($fieldType, $this->mappingTypes)) {
                            throw new Exception\InvalidArgumentException(
                                sprintf(
                                    '%s: expects type of (\'%s\'), received "%s"',
                                    __METHOD__,
                                    implode("','", $this->mappingTypes),
                                    (is_object($fieldType) ? get_class($fieldType) : $fieldType)
                                )
                            );
                        }

                        if ($fieldMapping->offsetExists('parameters')) {
                            $fieldParameters = $fieldMapping->offsetGet('parameters');
                        }

                        $properties[$elasticsearchProperty] = array_merge(['type' => $fieldType], $fieldParameters->toArray());
                    }
                }
            }
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

        //var_dump($params);die();

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

        $id = null;
        $body = [];
        foreach ($config->get('fields') as $elasticsearchProperty => $fieldConfig) {
            if ($fieldConfig->offsetExists('mapping')) {
                $fieldMapping = $fieldConfig->offsetGet('mapping');
                if ($fieldMapping->offsetExists('type')) {
                    $fieldType = $fieldMapping->offsetGet('type');
                }
            }
            if ($fieldConfig->offsetExists('indexing')) {
                $fieldIndexing = $fieldConfig->offsetGet('indexing');
                if ($fieldType && $fieldType == 'completion') {
                    if ($fieldIndexing->offsetExists('input')) {
                        $completionInput = $fieldIndexing->offsetGet('input');
                        if ($completionInput->offsetExists('attribute')) {
                            $attribute = $completionInput->offsetGet('attribute');
                            if (method_exists($entity, $method = ('get' . ucfirst($attribute)))) {
                                $body[$elasticsearchProperty]['input'] = $entity->$method();
                            } else {
                                throw new Exception\InvalidArgumentException(
                                    sprintf(
                                        '%s: Entity of type "%s" has no method "%s()"',
                                        __METHOD__,
                                        get_class($entity),
                                        $method
                                    )
                                );
                            }
                        } elseif ($completionInput->offsetExists('attributes')) {
                            $attributes = $completionInput->offsetGet('attributes');
                            foreach ($attributes as $attribute) {
                                if (method_exists($entity, $method = ('get' . ucfirst($attribute)))) {
                                    $body[$elasticsearchProperty]['input'][] = $entity->$method();
                                } else {
                                    throw new Exception\InvalidArgumentException(
                                        sprintf(
                                            '%s: Entity of type "%s" has no method "%s()"',
                                            __METHOD__,
                                            get_class($entity),
                                            $method
                                        )
                                    );
                                }
                            }
                            //var_dump($elasticsearchProperty, $attributes);
                            //die('multi attributes');
                        }
                        //var_dump($completionInput);
                    }
                    if ($fieldIndexing->offsetExists('output')) {
                        $completionOutput = $fieldIndexing->offsetGet('output');
                        if ($completionOutput instanceof \Traversable && $completionOutput->offsetExists('modifier')) {
                            $modifier = $completionOutput->offsetGet('modifier');
                            if ($modifier->offsetExists('sprintf')) {
                                $sprintfModifier = $modifier->offsetGet('sprintf')->toArray();
                                $modifierAttributes = [];
                                foreach ($sprintfModifier[1] as $modifierAttribute) {
                                    if (method_exists($entity, $method = ('get' . ucfirst($modifierAttribute)))) {
                                        $modifierAttributes[] = $entity->$method();
                                    } else {
                                        throw new Exception\InvalidArgumentException(
                                            sprintf(
                                                '%s: Entity of type "%s" has no method "%s()"',
                                                __METHOD__,
                                                get_class($entity),
                                                $method
                                            )
                                        );
                                    }
                                }
                                //var_dump($sprintfModifier, vsprintf($sprintfModifier[0], $modifierAttributes));die();
                                $body[$elasticsearchProperty]['output'] = vsprintf($sprintfModifier[0], $modifierAttributes);
                            }
                        }
                    }
                } else {
                    if ($fieldIndexing->offsetExists('attribute')) {
                        $fieldIndexingAttribute = $fieldIndexing->offsetGet('attribute');
                        if (method_exists($entity, $method = ('get' . ucfirst($fieldIndexingAttribute)))) {
                            if ($elasticsearchProperty == 'id') {
                                $id = $entity->$method();
                            } else {
                                $body[$elasticsearchProperty] = $entity->$method();
                            }
                        } else {
                            throw new Exception\InvalidArgumentException(
                                sprintf(
                                    '%s: Entity of type "%s" has no method "%s()"',
                                    __METHOD__,
                                    get_class($entity),
                                    $method
                                )
                            );
                        }
                    }
                }
            }
        }

        //var_dump($body);
        //die("INSERT");

        $params = [
            'index' => $config->get('index'),
            'type'  => $config->get('type'),
            'body'  => $body
        ];

        if (!is_null($id)) {
            $params['id'] = $id;
        }

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
     * @return object|\Doctrine\ORM\Mapping\Column
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
