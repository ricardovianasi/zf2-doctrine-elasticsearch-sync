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

        //echo "<pre>";
        //print_r($params);
        //echo "</pre>";

        $this->elasticsearchClient->indices()->create($params);

        /*
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
        */
    }

    private function insertEntity($entity, Option\Entity $entityOptions)
    {
        $id = null;
        $body = [];
        foreach ($entityOptions->getFields() as $property => $entityOption) {
            if ($entityOption->getIndexing()) {
                $indexing = $entityOption->getIndexing();
                $body[$property] = $indexing->getElasticsearchConfig($entity);

                //var_dump($indexing->getElasticsearchConfig($entity));




                /*
                if (is_null($indexing->getCallable()) && is_null($indexing->getAttribute())) {
                    throw new Exception\InvalidArgumentException(
                        sprintf(
                            '%s: Property "%s" in entity of type "%s" must have one indexing option. Please define a callable OR an attribute."',
                            __METHOD__,
                            $property,
                            get_class($entity)
                        )
                    );
                }

                if ($indexing->getCallable() && $indexing->getAttribute()) {
                    throw new Exception\InvalidArgumentException(
                        sprintf(
                            '%s: Property "%s" in entity of type "%s" should have only one indexing option. Please define a callable OR an attribute."',
                            __METHOD__,
                            $property,
                            get_class($entity)
                        )
                    );
                }

                if (!is_null($indexing->getCallable())) {
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
                /*
                die();


                if (method_exists($entity, $method = ('get' . ucfirst($indexing->getAttribute())))) {
                    if ($property == 'id') {
                        $id = $entity->$method();
                    } else {
                        $body[$property] = $entity->$method();
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
                */
            }
        }

        $params = [
            'index' => $entityOptions->getIndex(),
            'type'  => $entityOptions->getType(),
            'body'  => $body
        ];

        echo "<pre>";
        print_r($params);
        echo "</pre>";
        //die();

        if (!is_null($id)) {
            $params['id'] = $id;
        }

        $this->elasticsearchClient->index($params);


        /*
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
        */
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
