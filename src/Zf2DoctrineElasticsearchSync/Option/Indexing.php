<?php
namespace Zf2DoctrineElasticsearchSync\Option;

use Zend\Stdlib\AbstractOptions;

class Indexing extends AbstractOptions
{
    /** @var  String */
    private $attribute;

    /**
     * Getter für Attribut attribute
     *
     * @return String
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param String $attribute
     *
     * @return Indexing
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }


}