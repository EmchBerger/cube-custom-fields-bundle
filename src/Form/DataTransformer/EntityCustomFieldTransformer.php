<?php

namespace CubeTools\CubeCustomFieldsBundle\Form\DataTransformer;

use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManager;

/**
 * Transformer for EntityCustomField
 */
class EntityCustomFieldTransformer implements DataTransformerInterface
{
    private $em;
    private $fieldType;
    private $reverseAsString;

    public function __construct(EntityManager $em, $fieldType, $reverseAsString = false)
    {
        $this->em = $em;
        $this->fieldType = $fieldType;
        $this->reverseAsString = $reverseAsString;
    }

    /**
     * No real transformation required in forward direction
     *
     * @return mixed An array of entities
     *
     * @throws TransformationFailedException
     */
    public function transform($collection)
    {
        return $collection;
    }

    /**
     * Reverse transformation
     *
     * @param mixed $array An array of entities
     *
     * @return mixed contains either the id of the element (as string) or the element itself, depending on the usage scenario
     */
    public function reverseTransform($array)
    {
        if ($this->reverseAsString && $this->fieldType == 'Tetranz\Select2EntityBundle\Form\Type\Select2EntityType') {
            $idArray = array();
            if (is_array($array) || $array instanceof \ArrayAccess) {
                foreach ($array as $elem) {
                    $idArray[] = (string) $elem->getId();
                }
            } else {
                if ($array) {
                    return $idArray[] = (string) $array->getId();
                }
            }

            if (array_key_exists(0, $idArray)) {
                return $idArray[0];
            } else {
                return '';
            }
        } else {
            // no reverse transformation required
            return $array;
        }
    }
}
