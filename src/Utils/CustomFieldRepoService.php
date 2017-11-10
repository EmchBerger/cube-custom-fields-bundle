<?php

namespace CubeTools\CubeCustomFieldsBundle\Utils;

use CubeTools\CubeCustomFieldsBundle\EntityHelper\EntityMapper;
use Doctrine\ORM\EntityManager;

/**
 * This service allows to get IDs of customField entities (base entities)
 * by querying.
 */
class CustomFieldRepoService
{
    private $configReader;
    private $em;

    public function __construct(ConfigReader $configReader, EntityManager $em)
    {
        $this->configReader = $configReader;
        $this->em = $em;
    }

    /**
     * Retrieves all entities from a given class which are linked to a specific CustomField
     *
     * @param string $entityClass   Must be an entity stored in the database
     * @param int    $customFieldId The ID of the customField base entity to match with
     *
     * @return array        Contains all found customField entities, which point to $object
     */
    public function getEntitiesIdsForCustomFieldId($entityClass, $customFieldId)
    {
        $qb = $this->em->getRepository($entityClass)->createQueryBuilder('e'); //->findBy(array('customFields', $customField));
        $qb->join('e.customFields', 'cf')
           ->select('e.id')
           ->where('cf.id = :id')
           ->setParameter('id', $customFieldId);
        $result = $qb->getQuery()->getScalarResult();

        return array_column($result, 'id');
    }

    /**
     * Retrieves all customField entities IDs (with fieldId = $fieldId) which point to $object
     *
     * @param type $fieldId The identifier of the customField to search through
     * @param type $object  Must be an entity stored in the database
     *
     * @return array        Contains all found customField entities IDs, which point to $object
     */
    public function getCustomFieldEntitiesIdsForObject($fieldId, $object)
    {
        $entities = $this->getCustomFieldEntitiesForObject($fieldId, $object);
        $ids = array();
        foreach ($entities as $entity) {
            $ids[] = $entity->getId();
        }

        return $ids;
    }

    /**
     * Retrieves all customField entities (with fieldId = $fieldId) which point to $object
     *
     * @param type $fieldId The identifier of the customField to search through
     * @param type $object  Must be an entity stored in the database
     *
     * @return array        Contains all found customField entities, which point to $object
     */
    public function getCustomFieldEntitiesForObject($fieldId, $object)
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
                $er = $this->em->getRepository($entityClass);
                break;

            default:
                $er = $this->em->getRepository('CubeTools\CubeCustomFieldsBundle\Entity\EntityCustomField');
                break;
        }

        // retrieve the customField entities from the database
        if ($simpleQuery) {
            $dbField = $entityClass::getStorageFieldName();
            $containingCustomFields = $er->createQueryBuilder('cf')
                    ->andWhere('cf.fieldId = :fieldId')
                    ->andWhere('cf.'.$dbField.' LIKE :object')
                    ->setParameters(array(
                        'fieldId' => $fieldId,
                        'object' => '%'.$object.'%',
                    ))->getQuery()->getResult()
            ;
        } else {
            $customFieldEntities = $er->findBy(array('fieldId' => $fieldId));
            // traverse the customField entities and check if the $object is contained
            $containingCustomFields = array();
            foreach ($customFieldEntities as $cfEntity) {
                if ($cfEntity->isEmpty()) {
                    // empty values can occur if the cleanup of empty custom fields is not correctly done
                    continue;
                }
                $cfEntityVal = $cfEntity->getValue();
                if (is_array($cfEntityVal) || $cfEntityVal instanceof \ArrayAccess) {
                    // the customField contains an array of entities
                    foreach ($cfEntityVal as $content) {
                        // we filter by an object
                        if ($content && self::compareObjects($content, $object)) {
                            $containingCustomFields[] = $cfEntity;
                            break;
                        }
                    }
                } else {
                    // the customField contains a single entity
                    if (self::compareObjects($object, $cfEntityVal)) {
                        $containingCustomFields[] = $cfEntity;
                    }
                }
            }
        }

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
