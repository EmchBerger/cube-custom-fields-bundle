<?php

namespace CubeTools\CubeCustomFieldsBundle\EntityHelper;

use CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase;
use Doctrine\Common\Collections\Collection;

/**
 * Handles a collection for CustomFieldBase entities.
 *
 * @internal
 *
 * Creates new (unsaved) entities if required;
 */
class CustomFieldsGetSet
{
    /**
     * Returns a new UnsavedCustomField if the key does not exist.
     *
     * @param object $owningEntity
     * @param string $key
     *
     * @return CustomFieldBase
     */
    public static function getField($owningEntity, $key)
    {
        $entity = $owningEntity->getNonemptyCustomFields()->get($key);

        if (!$entity) {
            // TODO: here we need to check whether the fieldId is available for the entity at all (based on the configuration)
            $entity = new UnsavedCustomField();
            $entity->setFieldId($key);
        }

        return $entity;
    }

    /**
     * Gets the value of a custom field, null if not set.
     *
     * @param object $owningEntity
     * @param string $key
     *
     * @return mixed
     */
    public static function getValue($owningEntity, $key)
    {
        $entity = $owningEntity->getNonemptyCustomFields()->get($key);

        if ($entity) {
            $value = $entity->getValue();
        } else {
            // TODO: here we need to check whether the fieldId is available for the entity at all (based on the configuration)
            $value = null;
        }

        return $value;
    }

    /**
     * Sets a custom field, keeping only set values in $customFields.
     *
     * @param object          $owningEntity
     * @param string          $key
     * @param CustomFieldBase $entity
     */
    public function setField($owningEntity, $key, CustomFieldBase $entity)
    {
        if ($entity->isEmpty()) {
            self::remove($owningEntity, $key);

            return;
        }
        /** @var Collection */
        $customFields = $owningEntity->getNonemptyCustomFields();
        if ($entity instanceof UnsavedCustomField) {
            $entity = self::createRealEntity($entity);
            $entity->setFieldId($key);
        } elseif ($entity->getFieldId() !== $key) {
            $entity = clone $entity;
            $entity->setFieldId($key);
        }
        $customFields->set($key, $entity);
    }

    /**
     * Sets the value of a custom field, keeping only set values in $customFields.
     *
     * @param object $owningEntity
     * @param string $key
     * @param mixed  $value
     */
    public function setValue($owningEntity, $key, $value)
    {
        if (!$value) {
            self::remove($owningEntity, $key);

            return;
        }
        $field = self::getField($owningEntity, $key);
        $field->setValue($value);
        self::setField($owningEntity, $key, $field);
    }

    public static function remove($owningEntity, $key)
    {
        // do not save empty entities
        /*
         *  TODO: this does not remove CustomFieldBase entities for EntityCustomField collections!
         *  Only the link between entity and the collection is removed (which is enough for correct functionality, but leads to dead data in the database)
         */
        $owningEntity->getNonemptyCustomFields()->remove($key);
    }

    /**
     * Creates a real entity from the unsaved one.
     *
     * @param \CubeTools\CubeCustomFieldsBundle\EntityHelper\UnsavedCustomField $tempEntity
     *
     * @return \CubeTools\CubeCustomFieldsBundle\Entity\*CustomField
     *
     * @throws \InvalidArgumentException
     */
    private static function createRealEntity(UnsavedCustomField $tempEntity)
    {
        $value = $tempEntity->getValue();
        $formType = $tempEntity->getType();
        $customFieldType = EntityMapper::getCustomFieldClass($formType);
        $entity = new $customFieldType();
        $entity->setValue($value);
        // do not set $entity->setFieldId($tempEntity->getFieldId), is set later anyway

        return $entity;
    }
}
