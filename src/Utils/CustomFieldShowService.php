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
            $showCfg = array(
                'label' => isset($config['label']) ? $config['label'] : $fieldId,
            );
            if (isset($fieldConfig['field_options']['multiple']) && $fieldConfig['field_options']['multiple']) {
                $showCfg['value'] = $field->getValue();
            } else {
                $showCfg['value'] = array($field->getValue());
            }
            $showCfg['raw'] = ($fieldConfig['type'] == "Ivory\CKEditorBundle\Form\Type\CKEditorType");
            $customFields[$fieldId] = $showCfg;
        }

        return $customFields;
    }
}
