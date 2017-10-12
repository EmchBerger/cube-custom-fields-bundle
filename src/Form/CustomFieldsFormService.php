<?php

namespace CubeTools\CubeCustomFieldsBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class CustomFieldsFormService
{
    private $fieldsConfig = null;

    private $typeMap = array(
        'text' => TextType::class,
        'date' => DateType::class,
        // TODO add more types
    );

    /**
     * Constructor of service.
     *
     * @param array $fieldsConfig Configuration of entities with CustomFields from the bundles configuration.
     */
    public function __construct(array $fieldsConfig)
    {
        $this->fieldsConfig = $fieldsConfig;
    }

    /**
     * Add all Custom Fields to the form.
     *
     * @param FormBuilderInterface $form      the form to add the entities to
     * @param string               $dataClass entity to set the fields for, only when forms data_class is not set.
     *
     * @throws \LogicException when wrong configured
     */
    public function addCustomFields(FormBuilderInterface $form, $dataClass = null)
    {
        $entityClass = $form->getFormConfig()->getOption('data_class');
        if (null !== $entityClass && null !== $dataClass && $dataClass !== $entityClass) {
            throw new \LogicException('Do not set $dataClass if forms option data_class is set.');
        } elseif (null !== $dataClass) {
            $entityClass = $dataClass;
        } elseif (null === $entityClass) {
            throw new \LogicException('Do set $dataClass if form has not option data_class set.');
        }

        if (!isset($this->fieldsConfig[$entityClass])) {
            return; // nothing to do
        }
        $fields = $this->fieldsConfig[$entityClass];

        foreach ($fields as $name => $field) {
            $options = array(
                'property_path' => "customFields[$name].value",
            );
            if (isset($field['field_label'])) {
                $options['label'] = $field['field_label'];
            }
            $fieldType = $field['field_type'];
            if (isset($this->typeMap[$fieldType])) {
                $type = $this->typeMap[$fieldType];
            } else {
                throw new \LogicException(sprintf('type %s is not supported by %s', $fieldType, __CLASS__));
            }
            switch ($fieldType) {
                case 'select':
                    // TODO $options['choices'] = ...
                    break;
                // TODO other special cases
            }
            $form->add($name, $type, $options);
        }
    }
}
