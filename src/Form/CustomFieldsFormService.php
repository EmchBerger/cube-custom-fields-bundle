<?php

namespace CubeTools\CubeCustomFieldsBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class CustomFieldsFormService
{
    private $fieldsConfig = null;

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
     * @param FormBuilderInterface $form        the form to add the entities to
     * @param string               $entityClass entity to set the fields for, only when form is not an EntityType
     *
     * @throws \LogicException when wrong configured
     */
    public function addCustomFields(FormBuilderInterface $form, $entityClass = null)
    {
        $confEntityClass = $form->getFormConfig()->getOption('class');
        if (null !== $confEntityClass && null !== $entityClass) {
            throw new \LogicException('Do not set $entityClass is form is an EntityType');
        } elseif (null !== $confEntityClass) {
            $entityClass = $confEntityClass;
        } elseif (null === $entityClass) {
            throw new \LogicException('Do set $entityClass is form is not an EntityType');
        }

        if (!isset($this->fieldsConfig[$entityClass])) {
            return; // nothing to do
        }
        $fields = $this->fieldsConfig[$entityClass];

        foreach ($fields as $name => $field) {
            $options = array();
            if (isset($field['field_label'])) {
                $options['label'] = $field['field_label'];
            }
            switch ($field['field_type']) {
                case 'text':
                    $type = TextType::class;
                    break;
                case 'date':
                    $type = DateType::class;
                    break;
                // TODO add more types
                default:
                    throw new \LogicException(sprintf('type %s is not supported by %s', $field['field_type'], __CLASS__));
            }
            $form->add($name, $type, $options);
        }
    }
}
