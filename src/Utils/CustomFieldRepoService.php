<?php

namespace CubeTools\CubeCustomFieldsBundle\Utils;

use CubeTools\CubeCustomFieldsBundle\EntityHelper\EntityMapper;
use Doctrine\ORM\EntityManager;

/*
 * This service allows to get IDs of customField entities (base entities)
 * by querying.
 */

class CustomFieldRepoService
{
    public function __construct(ConfigReader $configReader, EntityManager $em)
    {
        $this->configReader = $configReader;
        $this->em = $em;
    }

    /**
     * Retrieves all customField entities IDs (with fieldId = $fieldId) which point to $object
     * @param type $fieldId The identifier of the customField to search through
     * @param type $object  Must be an entity stored in the database
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
     * @param type $fieldId The identifier of the customField to search through
     * @param type $object  Must be an entity stored in the database
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
        switch ($entityClass) {
            case 'CubeTools\CubeCustomFieldsBundle\Entity\TextCustomField':
                $er = $this->em->getRepository('CubeTools\CubeCustomFieldsBundle\Entity\TextCustomField');
                break;

            case 'CubeTools\CubeCustomFieldsBundle\Entity\TextareaCustomField':
                $er = $this->em->getRepository('CubeTools\CubeCustomFieldsBundle\Entity\TextareaCustomField');
                break;

            case 'CubeTools\CubeCustomFieldsBundle\Entity\DatetimeCustomField';
                $er = $this->em->getRepository('CubeTools\CubeCustomFieldsBundle\Entity\DatetimeCustomField');
                break;

            default:
                $er = $this->em->getRepository('CubeTools\CubeCustomFieldsBundle\Entity\EntityCustomField');
                break;
        }

        // retrieve the customField entities from the database
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
                    if ($content && ($object == $content)) {
                        $containingCustomFields[] = $cfEntity;
                        break;
                    }
                }
            } else {
                // the customField contains a single entity
                if ($object == $cfEntityVal) {
                    $containingCustomFields[] = $cfEntity;
                }
            }
        }

        return $containingCustomFields;
    }

    /**
     * returns the ID of an object
     * @param \ArrayAccess $object
     * @return \ArrayAccess
     */
    private static function getIdOfObject($object)
    {
        if (is_array($object) || $object instanceof \ArrayAccess) {
            if (array_key_exists('id', $object)) {
                return $object['id'];
            }
        }
        if (method_exists($object, 'getId')) {
            return $object->getId();
        }
        return null;
    }
}