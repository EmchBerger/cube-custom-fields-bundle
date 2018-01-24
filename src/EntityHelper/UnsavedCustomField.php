<?php

namespace CubeTools\CubeCustomFieldsBundle\EntityHelper;

use CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase;

/**
 * Class to keep the CustomField values when not set yet
 */
class UnsavedCustomField extends CustomFieldBase
{
    private $tempValue;

    private $config;

    public function __construct()
    {
        // TODO: find a better way to retrieve the parameters from custom_fields.yml
        global $kernel;
        $this->config = $kernel->getContainer()->getParameter('cubetools.customfields.entities');
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->tempValue;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->tempValue = $value;

        return $this;
    }


    /**
     * Returns the type according to the configuration
     *
     * @return string
     */
    public function getType()
    {
        // traverse the config and return the type of the first matching element
        $fieldId = $this->getFieldId();
        foreach ($this->config as $entity) {
            if (isset($entity[$fieldId])) {
                return $entity[$fieldId]['type'];
            }
        }

        return null;
    }


    public static function getStorageFieldName()
    {
        return 'tempValue';
    }
}
