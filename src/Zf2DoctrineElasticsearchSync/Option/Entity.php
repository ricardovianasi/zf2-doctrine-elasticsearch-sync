<?php
namespace Zf2DoctrineElasticsearchSync\Option;

use Zend\Stdlib\AbstractOptions;

class Entity extends AbstractOptions
{
    /** @var String */
    private $index;

    /** @var String */
    private $type;

    /** @var String */
    private $alias;

    /** @var Field[] */
    private $fields;

    /**
     * Getter für Attribut index
     *
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     *
     * @return Entity
     */
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     * Getter für Attribut type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return Entity
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Getter für Attribut fields
     *
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param Field[] $fields
     *
     * @return Entity
     */
    public function setFields($fields)
    {
        foreach ($fields as $fieldName => $fieldOptions) {
            if (isset($fieldOptions['type'])) {
                if ($fieldOptions['type'] == Field\CompletionSuggester::class) {
                    $this->fields[$fieldName] = new Field\CompletionSuggester($fieldOptions);
                }
            } else {
                $this->fields[$fieldName] = new Field\Field($fieldOptions);
            }
        }

        return $this;
    }

    /**
     * @param $field
     *
     * @return bool
     * @author Fabian Köstring
     */
    public function hasField($field)
    {
        if (array_key_exists($field, $this->getFields())) {
            return true;
        }
        return false;
    }

    /**
     * @param $field
     *
     * @return null|Field
     * @author Fabian Köstring
     */
    public function getField($field)
    {
        if ($this->hasField($field)) {
            return $this->getFields()[$field];
        }
        return null;
    }

    /**
     * Getter für Attribut alias
     *
     * @return String
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param String $alias
     *
     * @return Entity
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }
}