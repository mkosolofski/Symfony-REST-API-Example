<?php

namespace LOM\APIBundle\Parser;
use Nelmio\ApiDocBundle\Parser\ParserInterface;
use Nelmio\ApiDocBundle\Parser\JmsMetadataParser;
use Nelmio\ApiDocBundle\Parser\ValidationParser;

/**
 * Uses the JMS metadata factory to extract input/output model information
 */
class Api implements ParserInterface
{
    public function __construct(
        JmsMetadataParser $jmsParser,
        ValidationParser $validationParser
    ) {
        $this->jmsParser = $jmsParser;
        $this->validationParser = $validationParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(array $input)
    {
        return array_key_exists('parsers', $input)
            && in_array(__CLASS__, $input['parsers'])
            && (
                $this->jmsParser->supports($input)
                || $this->validationParser->supports($input)
            );
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $input)
    {
        $params = array();
        if ($this->jmsParser->supports($input)) {
            $params = $this->jmsParser->parse($input);
        }

        if ($this->validationParser->supports($input)) {
            foreach($this->validationParser->parse($input) as $paramName => $validationParam) {
                if (!array_key_exists($paramName, $params)) continue;

                if (array_key_exists('required', $validationParam)) {
                    $params[$paramName]['required'] = $validationParam['required'];
                }
 
                if (array_key_exists('format', $validationParam)) {
                    $params[$paramName]['format'] = $validationParam['format'];
                }
            }
        }

        foreach ($params as $name => $param) {
            if (!array_key_exists('class', $param)) continue;
            $params[$name]['children'] = $this->parse(
                array(
                    'class'  => $param['class'],
                    'groups' => $input['groups']
                )
            );
        }

        return $params;
    }
}
