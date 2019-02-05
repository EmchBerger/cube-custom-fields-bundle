<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

/**
 * DoctrineRepository for normal CustomField entities.
 */
class GeneralCustomFieldRepository extends AbstractCustomFieldRepository
{
    // doc in parent class
    public function addFindByObject($qb, $cfAlias, $object, $fieldId)
    {
        $entityClass = $this->getClassName();
        $dbField = $entityClass::getStorageFieldName();
        $qb
            ->andWhere($cfAlias.'.fieldId = :fieldId')
            ->andWhere($cfAlias.'.'.$dbField.' LIKE :object')
            ->setParameters(array(
                'fieldId' => $fieldId,
                'object' => '%'.$object.'%',
            ))
        ;
    }
}
