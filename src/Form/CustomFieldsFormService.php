<?php

namespace CubeTools\CubeCustomFieldsBundle\Form;

use CubeTools\CubeCustomFieldsBundle\EntityHelper\EntityMapper;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forminterface;

class CustomFieldsFormService
{
    private $fieldsConfig = null;
    private $em;

    /**
     * Constructor of service.
     *
     * @param array $fieldsConfig Configuration of entities with CustomFields from the bundles configuration.
     */
    public function __construct(array $fieldsConfig, EntityManager $em)
    {
        $this->fieldsConfig = $fieldsConfig;
        $this->em = $em;
    }

    /**
     * Add all Custom Fields to the form.
     *
     * @param FormBuilderInterface|FormInterface $form      the form to add the entities to
     * @param string                             $dataClass entity to set the fields for, only when forms data_class is not set.
     *
     * @throws \LogicException when wrong configured
     */
    public function addCustomFields($form, $dataClass = null)
    {
        if ($form instanceof Forminterface) {
            $entityClass = $form->getConfig()->getOption('data_class');
        } elseif ($form instanceof FormBuilderInterface) {
            $entityClass = $form->getFormConfig()->getOption('data_class');
        } else {
            throw new \InvalidArgumentException(sprintf(
                '$form must be instance of %s or %s, its class is %s',
                Forminterface::class,
                FormBuilderInterface::class,
                get_class($form)
            ));
        }

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
                'by_reference' => false,
                'translation_domain' => 'custom_fields',
            );
            if (isset($field['label'])) {
                $options['label'] = $field['label'];
            }
            $fieldType = $field['type'];

            if (isset($field['field_options'])) {
                $options = array_merge($options, $field['field_options']);
            }
            $form->add($name, $fieldType, $options);
            if (EntityMapper::isEntityField($field['type'])) {
                $form->get($name)->addModelTransformer( new \CubeTools\CubeCustomFieldsBundle\EntityHelper\EntityCustomFieldTransformer($this->em)) ;
            }
        }
    }
}
