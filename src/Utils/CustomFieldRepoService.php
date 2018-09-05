<?php

namespace CubeTools\CubeCustomFieldsBundle\Utils;

use CubeTools\CubeCustomFieldsBundle\EntityHelper\EntityMapper;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * This service allows to get IDs of customField entities (base entities)
 * by querying.
 */
class CustomFieldRepoService
{
    private $configReader;
    private $mr;

    /**
     * @var int number of usages of parameter (preventing overwrite of previous value)
     */
    protected $parameterCount = 0;

    public function __construct(ConfigReader $configReader, ManagerRegistry $mr)
    {
        $this->configReader = $configReader;
        $this->mr = $mr;
    }

    /**
     * Retrieves all entities from a given class which are linked to a specific CustomField
     *
     * @param string $entityClass   Must be an entity stored in the database
     * @param int    $customFieldId The ID of the customField base entity to match with
     *
     * @return array        Contains all found entity IDs which point to the customFieldId
     */
    public function getEntitiesIdsForCustomFieldId($entityClass, $customFieldId)
    {
        $qb = $this->mr->getManager()->getRepository($entityClass)->createQueryBuilder('e');
        $qb->join('e.customFields', 'cf')
           ->select('e.id')
           ->where('cf.id = :id')
           ->setParameter('id', $customFieldId);
        $result = $qb->getQuery()->getScalarResult();

        return array_column($result, 'id');
    }

    /**
     * @param string $customFieldId
     * @param string $firstRootAlias
     * @param \Doctrine\ORM\QueryBuilder $qb
     *
     * @return array id of entities, which fulfil any condition
     */
    protected function addAnyCustomFieldIdQueryResult($customFieldId, $firstRootAlias, $qb)
    {
        $qbCloned = clone $qb;
        if (!in_array('cf', $qb->getAllAliases())) {
            $qbCloned->join($firstRootAlias . '.customFields', 'cf');
        }
        $qbCloned->andWhere('cf.fieldId = :fieldId')
            ->setParameter('fieldId', $customFieldId);

        return $qbCloned->getQuery()->getResult();
    }

    /**
     * Method looking for records, where any value of custom field is set.
     *
     * @param string $customFieldId
     * @param string $firstRootAlias
     * @param \Doctrine\ORM\QueryBuilder $qb
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function addAnyCustomFieldId($customFieldId, $firstRootAlias, $qb)
    {
        $alias = 'cf' . $this->parameterCount;
        $qb->join($firstRootAlias . '.customFields', $alias);
        $qb->andWhere($alias . '.fieldId = :fieldId' . $this->parameterCount);
        $qb->setParameter('fieldId' . $this->parameterCount, $customFieldId);
        $this->parameterCount++;

        return $qb;
    }

    /**
     * Method looking for records, where no value of custom field is set.
     *
     * @param string $customFieldId
     * @param string $firstRootAlias
     * @param \Doctrine\ORM\QueryBuilder $qb
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function addNoneCustomFieldId($customFieldId, $firstRootAlias, $qb)
    {
        $entities = $this->addAnyCustomFieldIdQueryResult($customFieldId, $firstRootAlias, $qb);
        $qb->andWhere($firstRootAlias . sprintf('.id NOT IN (:notAny%s)', $this->parameterCount))
            ->setParameter('notAny' . $this->parameterCount, $entities);
        $this->parameterCount++;

        return $qb;
    }

    /**
     * Retrieves all entities from a given class which are linked to a set of CustomField
     *
     * @param string $entityClass   Must be an entity stored in the database
     * @param array  $customFieldIds The array of IDs of the customField base entities to match with
     *
     * @return array Contains all found entity IDs which point to any of the customFields in customFieldIds
     */
    public function getEntitiesIdsForCustomFieldIds($entityClass, $customFieldIds)
    {
        if (!count($customFieldIds)) {
            return array();
        }

        $qb = $this->getEntitiesIdsForCustomFieldIdsQb($entityClass, $customFieldIds);

        $result = $qb->getQuery()->getScalarResult();

        return array_column($result, 'id');
    }

    /**
     * Add an IN-criterion to an existing querybuilder to only allow queryField to be in set of entities referring to given customFields
     * @param DoctrineQueryBuilder $qb
     * @param string $queryField
     * @param string $entityClass
     * @param array $customFieldIds
     *
     * @return DoctrineQueryBuilder $qb
     */
    public function addWhereInIdsForCustomFieldIds($qb, $queryField, $entityClass, $customFieldIds)
    {
        if (!count($customFieldIds)) {
            $qb->andWhere('TRUE = FALSE');
        } else {
            $qb->andWhere($qb->expr()->in($queryField, $this->getEntitiesIdsForCustomFieldIdsQb($entityClass, $customFieldIds)->getDQL()));
        }

        return $qb;
    }

    /**
     * Add an IN-criterion to an existing querybuilder to also allow queryField to be in set of entities referring to given customFields
     * @param DoctrineQueryBuilder $qb
     * @param string $queryField
     * @param string $entityClass
     * @param array $customFieldIds
     *
     * @return DoctrineQueryBuilder $qb
     */
    public function addOrWhereInIdsForCustomFieldIds($qb, $queryField, $entityClass, $customFieldIds)
    {
        if (count($customFieldIds)) {
            $qb->orWhere($qb->expr()->in($queryField, $this->getEntitiesIdsForCustomFieldIdsQb($entityClass, $customFieldIds)->getDQL()));
        }

        return $qb;
    }

    private function getEntitiesIdsForCustomFieldIdsQb($entityClass, $customFieldIds)
    {
        $alias = 'entity_' . implode('', $customFieldIds) . mt_rand(0, 1000); //md5($entityClass . implode('_', $customFieldIds)); // make sure the alias is unique in the whole surrounding query (if any)
        $cfAlias = $alias . '_cf';
        $qb = $this->mr->getManager()->getRepository($entityClass)->createQueryBuilder($alias);
        $qb->join($alias . '.customFields', $cfAlias)
           ->select($alias . '.id')
           ->where($cfAlias . '.id IN (' . implode(',', $customFieldIds) . ')');

        return $qb;
    }

    /**
     * Retrieves all customField entities IDs (with fieldId = $fieldId) which point to $object
     *
     * @param int    $fieldId The identifier of the customField to search through
     * @param object $object  Must be an entity stored in the database
     *
     * @return array        Contains all found customField entities IDs, which point to $object
     */
    public function getCustomFieldEntitiesIdsForObject($fieldId, $object)
    {
        $customFields = $this->getCustomFieldEntitiesForObject($fieldId, $object, true);
        $ids = array_column($customFields, "id");

        return $ids;
    }

    /**
     * Retrieves all customField entities (with fieldId = $fieldId) which point to $object
     *
     * @param int    $fieldId The identifier of the customField to search through
     * @param object $object  Must be an entity stored in the database
     *
     * @return array        Contains all found customField entities, which point to $object
     */
    public function getCustomFieldEntitiesForObject($fieldId, $object, $idsOnly = false)
    {
        if (!($fieldId && $object)) {
            // if either of the two parameters is not set, we can skip the rest

            return array();
        }
        $config = $this->configReader->getConfigForFieldId($fieldId);
        $formType = $config['type'];
        $entityClass = EntityMapper::getCustomFieldClass($formType);
        $simpleQuery = false;
        switch ($entityClass) {
            case 'CubeTools\CubeCustomFieldsBundle\Entity\TextCustomField':
            case 'CubeTools\CubeCustomFieldsBundle\Entity\TextareaCustomField':
                $simpleQuery = true;
                // no break, set $er below
            case 'CubeTools\CubeCustomFieldsBundle\Entity\DatetimeCustomField':
                $er = $this->mr->getManager()->getRepository($entityClass);
                break;

            default:
                $er = $this->mr->getManager()->getRepository('CubeTools\CubeCustomFieldsBundle\Entity\EntityCustomField');
                break;
        }

        // retrieve the customField entities from the database
        $qb = $er->createQueryBuilder('cf');
        if ($simpleQuery) {
            // "simple" custom field entity types
            $dbField = $entityClass::getStorageFieldName();
            $qb->andWhere('cf.fieldId = :fieldId')
                ->andWhere('cf.'.$dbField.' LIKE :object')
                ->setParameters(array(
                    'fieldId' => $fieldId,
                    'object' => '%'.$object.'%',
                ));
        } else {
            // EntityCustomField
            $er->addFindByObject($qb, 'cf', $object, $fieldId);
        }
        if ($idsOnly) {
            $qb->select('cf.id');
            $returnVal = $qb->getQuery()->getScalarResult();
        } else {
            $returnVal = $qb->getQuery()->getResult();
        }

        return $returnVal;
    }

    /**
     * Retrieves all customField entities IDs (with fieldId = $fieldId) which point to something which resolves as $str
     * @param type $fieldId The identifier of the customField to search through
     * @param type $str  The string which must match the __toString() method of the custom field
     * @return array        Contains all found customField entities IDs, which point to $str
     */
    public function getCustomFieldEntitiesIdsForString($fieldId, $str)
    {
        $entities = $this->getCustomFieldEntitiesForString($fieldId, $str);
        $ids = array();
        foreach ($entities as $entity) {
            $ids[] = $entity->getId();
        }

        return $ids;
    }

    /**
     * Retrieves all customField entities (with fieldId = $fieldId) which point to something which resolves as $str
     * @param type $fieldId The identifier of the customField to search through
     * @param type $str     The string which must match the string representation of the custom field
     * @return array        Contains all found customField entities, which point to $str
     */
    public function getCustomFieldEntitiesForString($fieldId, $str)
    {
        if (!($fieldId && $str)) {
            // if either of the two parameters is not set, we can skip the rest

            return array();
        }
        $config = $this->configReader->getConfigForFieldId($fieldId);
        $formType = $config['type'];
        $entityClass = EntityMapper::getCustomFieldClass($formType);

        // retrieve the customField entities from the database
        $er = $this->mr->getManager()->getRepository($entityClass);
        $containingCustomFields = $er->createQueryBuilder('cf')
                ->andWhere('cf.fieldId = :fieldId')
                ->andWhere('cf.strRepresentation LIKE :strRepresentation')
                ->setParameters(array(
                    'fieldId' => $fieldId,
                    'strRepresentation' => '%' . $str . '%',
        ))->getQuery()->getResult();

        return $containingCustomFields;
    }

    private function compareObjects($a, $b)
    {
        if (is_object($a) && method_exists($a, 'getId') && is_object($b) && method_exists($b, 'getId')) {
            return $a->getId() == $b->getId();
        } else {
            return $a == $b;
        }
    }
}
