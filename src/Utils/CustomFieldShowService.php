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

    public function getDataNonempty($entity)
    {
        $fields = $entity->getNonemptyCustomFields();
        $entityClass = $this->configReader->getEntityClass($entity);

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

        $customFields = array();
        foreach ($this->configReader->getConfigForEntity($entity) as $fieldId => $fieldConfig) {
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
        $showCfg['raw'] = ($fieldConfig['type'] == "FOS\CKEditorBundle\Form\Type\CKEditorType");

        return $showCfg;
    }
}
