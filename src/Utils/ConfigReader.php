<?php

namespace CubeTools\CubeCustomFieldsBundle\Utils;


/* 
 * This service class allows access to the bundle configuration (custom_fields.yml)
 */

class ConfigReader
{
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getConfigForEntity($entityClass)
    {
        if (array_key_exists($entityClass, $this->config)) {
            return $this->config[$entityClass];
        }

        return array();
    }

    public function getConfigForFieldId($fieldId)
    {
        foreach ($this->config as $entityConfig) {
            if (array_key_exists($fieldId, $entityConfig)) {
                return $entityConfig[$fieldId];
            }
        }
        return array();
    }
}