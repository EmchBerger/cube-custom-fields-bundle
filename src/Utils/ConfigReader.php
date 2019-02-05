<?php

namespace CubeTools\CubeCustomFieldsBundle\Utils;

use CubeTools\CubeCustomFieldsBundle\EntityHelper\EntityMapper;
use Doctrine\Common\Util\ClassUtils;

/**
 * This service class allows access to the bundle configuration (custom_fields.yml)
 */
class ConfigReader
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Returns the real class of the entity.
     *
     * Proxy classes are resolved to the real entity class. Convinient for {@see ClassUtils::getClass()}
     *
     * @param object $entity object to get the class for
     *
     * @return string class of entity
     */
    public static function getEntityClass($entity)
    {
        return ClassUtils::getClass($entity);
    }

    /**
     * Returns the custom field config for the given entity
     *
     * @param object|string entity or class of entity
     *
     * @return array config of entity
     */
    public function getConfigForEntity($entity)
    {
        if (is_object($entity)) {
            $entityClass = self::getEntityClass($entity);
        } else {
            $entityClass = $entity;
        }
        if (array_key_exists($entityClass, $this->config)) {
            return $this->config[$entityClass];
        }

        return array();
    }

    public function getConfigForEntitesField($entity, $fieldId)
    {
        $entityConfig = $this->getConfigForEntity($entity);
        $config = array();
        if (isset($entityConfig[$fieldId])) {
            $config = $entityConfig[$fieldId];
        }

        return $config;
    }

    /**
     * @deprecated since version 1.3.6, use {@see getConfigForEntitesField}
     */
    public function getConfigForFieldId($fieldId)
    {
        @trigger_error(__METHOD__.' is deprecated, use getConfigForEntitesField() instead');
        foreach ($this->config as $entityConfig) {
            if (array_key_exists($fieldId, $entityConfig)) {
                return $entityConfig[$fieldId];
            }
        }

        return array();
    }

    /**
     * returns an array of class names (indexed by the respective fieldId) which are linked with custom fields
     */
    public function getLinkedEntities()
    {
        $linkedClasses = array();
        foreach ($this->config as $entityConfig) {
            foreach ($entityConfig as $fieldId => $field) {
                if (isset($field['type']) && EntityMapper::isEntityField($field['type'] && isset($field['field_options']) && isset($field['field_options']['class']))) {
                    $linkedClasses[] = array(
                        'fieldId' => $fieldId,
                        'class' => $field['field_options']['class'],
                    );
                }
            }
        }

        return $linkedClasses;
    }
}
