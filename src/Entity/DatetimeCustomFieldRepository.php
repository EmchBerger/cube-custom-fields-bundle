<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

/**
 * DoctrineRepository for DatetimeField entities.
 */
class DatetimeCustomFieldRepository extends AbstractCustomFieldRepository
{
    // doc in parent class
    public function addFindByObject($qb, $cfAlias, $object, $fieldId)
    {
        $entityClass = $this->getClassName();
        $dbField = $entityClass::getStorageFieldName();
        $value = $object;
        if ($value instanceof \DateTimeInterface) {
            $value = array('from' => $object, 'to' => $object);
        }
        $qb->andWhere($cfAlias.'.fieldId = :fieldId')->setParameter('fieldId', $fieldId);
        $param = 'dateValue';
        if ($value['from']) {
            $qb->andWhere($cfAlias.'.'.$dbField.' >= :'.$param.'From')->setParameter($param.'From', $value['from']);
        }
        if ($value['to']) {
            $qb->andWhere($cfAlias.'.'.$dbField.' < DATE_ADD(:'.$param."To, 1, 'DAY')")->setParameter($param.'To', $value['from']);
        }
    }
}
