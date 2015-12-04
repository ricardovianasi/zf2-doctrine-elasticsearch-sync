<?php
namespace Zf2DoctrineElasticsearchSync\Option\Indexing;

use Zend\Stdlib\AbstractOptions;
use Traversable;

class CompletionSuggester extends AbstractOptions
{
    /** @var  array */
    private $input;

    /** @var  String */
    private $output;

    /** @var  string */
    private $weight;

    /** @var  array */
    private $payload;

    /**
     * Getter für Attribut payload
     *
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     *
     * @return CompletionSuggester
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * Getter für Attribut weight
     *
     * @return String
     */
    public function getWeight() :string
    {
        return $this->weight;
    }

    /**
     * @param String $weight
     *
     * @return CompletionSuggester
     */
    public function setWeight(String $weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * Getter für Attribut output
     *
     * @return String
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param String $output
     *
     * @return CompletionSuggester
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * Getter für Attribut input
     *
     * @return array
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param array $input
     *
     * @return CompletionSuggester
     */
    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    public function getElasticsearchConfig($entity)
    {
        $result = [];

        // Input
        if (is_array($this->getInput()) OR $this->getInput() instanceof Traversable) {
            foreach ($this->getInput() as $input) {
                $inputCallable = $input;
                $result['input'][] = $entity->$inputCallable();
            }
        } else {
            $inputCallable = $this->getInput();
            $result['input'] = $entity->$inputCallable();
        }

        // Output
        $outputString = [];
        if (is_array($this->getOutput()) OR $this->getOutput() instanceof Traversable) {
            foreach ($this->getOutput() as $output) {
                $outputCallable = $output;
                $outputString[] = $entity->$outputCallable();
            }
        } else {
            $outputCallable = $this->getOutput();
            $outputString[] = $entity->$outputCallable();
        }
        if (!empty($outputString)) {
            $result['output'] = implode(" ", $outputString);
        }

        // Payload
        foreach ($this->getPayload() as $payloadKey => $payloadValue) {
            $payloadCallable = $payloadValue;
            $result['payload'][$payloadKey] = $entity->$payloadCallable();
        }

        // Weight
        if ($this->getWeight()) {
            $result['weight'] = $this->getWeight();
        }

        return $result;
    }
}