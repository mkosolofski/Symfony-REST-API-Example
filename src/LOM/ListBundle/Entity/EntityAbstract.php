<?php
/**
 * Contains LOM\ListBundle\Entity\ListEntity
 *
 * @package LOM\ListBundle
 * @subpackage Entity
 */

namespace LOM\ListBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

class EntityAbstract
{
    public function updateProperties($properties)
    {
        foreach($properties as $name => $value) {
            $method = 'set' . ucfirst($name);
            if (!method_exists($this, $method)) {
                $method = 'update' . ucfirst($name);
                if (!method_exists($this, $method)) continue;
            }
            $this->$method($value);
        }
    }

    public function toArray($ignoreNull = true)
    {
        $className = get_class($this);
        $result = array();
        $reflection = new \ReflectionClass($className);
        
        foreach ($reflection->getProperties() as $refProp) {
            if ($refProp->getDeclaringClass()->getName() != $className) continue;

            $propName = $refProp->getName();
            try {
                $method = $reflection->getMethod('get' . ucfirst($propName));
            } catch (\ReflectionException $e) {
                // Get method does not exist.
                continue;
            }
            
            if (!$method->isPublic()) continue;

            $value = $this->parsePropertyValue($method->invoke($this));
            if (is_null($value) && $ignoreNull) continue;
            $result[$propName] = $value;
        }

        return $result;
    }

    private function parsePropertyValue($propValue, $ignoreNull = true)
    {
        if ($propValue instanceof ArrayCollection) {
            return $this->parseArrayCollection($propValue);
        }

        if ($propValue instanceof EntityAbstract) {
            return $propValue->toArray($ignoreNull);
        }

        if (is_scalar($propValue)) {
            return $propValue;
        }

        return null;
    }

    private function parseArrayCollection(ArrayCollection $arrayCollection, $ignoreNull = true)
    {
        $properties = array();
        foreach($arrayCollection as $entity) {
            if (!($entity instanceof EntityAbstract)) break;
            $properties[] = $entity->toArray($ignoreNull);
        }
        return $properties;
    }
}
