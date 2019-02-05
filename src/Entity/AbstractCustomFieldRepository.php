<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Base class for *CustomFieldRepository.
 */
abstract class AbstractCustomFieldRepository extends EntityRepository
{
    /**
     * Finds EntityCustomFields which point to a specific object.
     *
     * @param object $object  value to filter for
     * @param string $fieldId optional
     *
     * @return \Doctrine\ORM\QueryBuilder $qb
     */
    public function findByObject($object, $fieldId = null)
    {
        $qb = $this->createQueryBuilder('cf');
        $this->addFindByObject($qb, 'cf', $object, $fieldId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string                     $cfAlias alias for table with custom field
     * @param object                     $object  value to filter for
     * @param string                     $fieldId optional
     *
     * @return \Doctrine\ORM\QueryBuilder $qb
     */
    abstract public function addFindByObject($qb, $cfAlias, $object, $fieldId);
}
