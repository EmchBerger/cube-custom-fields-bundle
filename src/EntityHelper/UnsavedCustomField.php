<?php

namespace CubeTools\CubeCustomFieldsBundle\EntityHelper;

use CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase;

/**
 * Class to keep the CustomField values when not set yet
 */
class UnsavedCustomField extends CustomFieldBase
{
    private $tempValue;

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

    public static function getStorageFieldName()
    {
        return 'tempValue';
    }
}
