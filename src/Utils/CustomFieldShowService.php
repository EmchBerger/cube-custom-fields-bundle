<?php

namespace CubeTools\CubeCustomFieldsBundle\Utils;

/**
 * This service allows to get required information from custom_fields table for showing.
 */
class CustomFieldShowService
{
    private $configReader;

    public function __construct(ConfigReader $configReader)
    {
        $this->configReader = $configReader;
    }

    public function getDataNonempt($entity)
    {
        $fields = $entity->getNonemptyCustomFields();
        $entityClass = get_class($entity);

        $customFields = array();
        foreach ($fields as $fieldId => $field) {
            $fieldConfig = $this->configReader->getConfigForEntitesField($entityClass, $field->getFieldId());
            if (!count($fieldConfig)) {
                continue;
            }
            $customFields[$fieldId] = $this->getConfigForField($fieldConfig, $field->getValue(), $fieldId);
        }

        return $customFields;
    }

    public function getDataAll($entity)
    {
        $fields = $entity->getNonemptyCustomFields();
        $entityClass = get_class($entity);

        $customFields = array();
        foreach ($this->configReader->getConfigForEntity($entityClass) as $fieldId => $fieldConfig) {
            if (isset($fields[$fieldId])) {
                $value = $fields[$fieldId]->getValue();
            } else {
                $value = null;
            }

            $customFields[$fieldId] = $this->getConfigForField($fieldConfig, $value, $fieldId);
        }

        return $customFields;
    }

    private function getConfigForField(array $fieldConfig, $value, $fieldId)
    {
        $showCfg = array(
            'label' => isset($fieldConfig['label']) ? $fieldConfig['label'] : $fieldId,
        );
        if (isset($fieldConfig['field_options']['multiple']) && $fieldConfig['field_options']['multiple']) {
            $showCfg['value'] = $value;
        } else {
            $showCfg['value'] = array($value);
        }
        $showCfg['raw'] = ($fieldConfig['type'] == "Ivory\CKEditorBundle\Form\Type\CKEditorType");

        return $showCfg;
    }
}
