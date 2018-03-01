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
     * Gets the customField, null if the key does not exist.
     *
     * @param object $owningEntity
     * @param string $key
     *
     * @return CustomFieldBase|null
     */
    public static function getField($owningEntity, $key)
    {
        $entity = $owningEntity->getNonemptyCustomFields()->get($key);

        if (!$entity) {
            self::checkCustomFieldExists($owningEntity, $key);
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
            self::checkCustomFieldExists($owningEntity, $key);
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
    public static function setField($owningEntity, $key, CustomFieldBase $entity)
    {
        if ($entity->isEmpty()) {
            self::remove($owningEntity, $key);

            return;
        }
        /** @var Collection */
        $customFields = $owningEntity->getNonemptyCustomFields();
        if ($entity->getFieldId() !== $key) {
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
    public static function setValue($owningEntity, $key, $value)
    {
        if (!$value) {
            self::remove($owningEntity, $key);

            return;
        }
        $field = self::getField($owningEntity, $key);
        if (is_null($field)) {
            $field = self::createNewEntity($owningEntity, $key);
        }
        $field->setValue($value);
        self::setField($owningEntity, $key, $field);
    }

    public static function remove($owningEntity, $key)
    {
        $owningEntity->getNonemptyCustomFields()->remove($key);
    }

    /**
     * Creates a new entity.
     *
     * @param object $owningEntity
     * @param string $key
     *
     * @return CustomFieldBase
     *
     * @throws \InvalidArgumentException
     */
    private static function createNewEntity($owningEntity, $key)
    {
        $formType = self::getEntityType($owningEntity, $key);
        $customFieldType = EntityMapper::getCustomFieldClass($formType);
        $entity = new $customFieldType();
        // do not set $entity->setFieldId($key) and ->setValue(), is set later

        return $entity;
    }

    /**
     * Returns the type according to the configuration.
     *
     * @param object $owningEntity
     * @param string $key
     *
     * @return string|null
     */
    private static function getEntityType($owningEntity, $key)
    {
        $config = self::getConfig();
        $owningClass = get_class($owningEntity);
        if (isset($config[$owningClass][$key])) {
            return $config[$owningClass][$key]['type'];
        }

        return null;
    }

    /**
     * Throws an error if the entity does not contain the custom field.
     *
     * @param object $owningEntity
     * @param string $key
     *
     * @throws \LogicException
     */
    private static function checkCustomFieldExists($owningEntity, $key)
    {
        if (is_null(self::getEntityType($owningEntity, $key))) {
            $msg = sprintf('CustomField "%s" does not exist for entity class "%s"', $key, get_class($owningEntity));
            throw new \LogicException($msg);
        }
    }

    /**
     * Get the customfields config.
     *
     * @global type $kernel
     *
     * @return array config of customFields
     */
    private static function getConfig()
    {
        global $kernel;

        // TODO: find a better way to retrieve the parameters from custom_fields.yml
        return $kernel->getContainer()->getParameter('cubetools.customfields.entities');
    }
}
