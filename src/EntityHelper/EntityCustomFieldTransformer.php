<?php

namespace CubeTools\CubeCustomFieldsBundle\EntityHelper;

use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManager;

/**
 * Transformer for EntityCustomField
 */
class EntityCustomFieldTransformer implements DataTransformerInterface
{
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    /**
     * Transforms a collection of EntityCustomField elements or a single EntityCustomField element into persisted (merged) entities
     *
     * @return mixed An array of entities
     *
     * @throws TransformationFailedException
     */
    public function transform($collection)
    {
        if (null === $collection) {
            return array();
        }
        if (is_array($collection) || $collection instanceof \ArrayAccess ) {
            // multiple entities
            // persist entities if required
            $mergedArray = array();
            foreach ($collection as $entity) {
                if (!$this->em->contains($entity)) {
                    $mergedArray[] = $this->em->merge($entity);
                }
            }
            return $mergedArray;
        } else {
            // single entity (the collection is an entity)
            return $this->em->merge($collection);
        }
    }
    /**
     * Reverse transformation not required. Is here only since defined in the abstract class.
     *
     * @param mixed $array An array of entities
     *
     * @return array
     */

    public function reverseTransform($array)
    {
        return $array; // no transformation required
    }
}
