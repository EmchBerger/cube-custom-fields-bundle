<?php

namespace CubeTools\CubeCustomFieldsBundle\Form;

use CubeTools\CubeCustomFieldsBundle\EntityHelper\EntityMapper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
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
     * @param array                              $overrideOptions optionally override form configuration options
     *
     * @throws \LogicException when wrong configured
     */
    public function addCustomFields($form, $dataClass = null, $overrideOptions = array())
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
            if (isset($field['filters']) && $fieldType != 'Tetranz\Select2EntityBundle\Form\Type\Select2EntityType') {
                // add repository method for filtering specific fieldIds (only makes sense for EntityType fields used as Selects)
                $filters = $field['filters'];
                $field['field_options']['query_builder'] = function (EntityRepository $er) use ($filters) {
                    $qb = $er->createQueryBuilder('customField');
                    foreach ($filters as $field => $value) {
                        $qb->andWhere(sprintf('customField.%s = :%s', $field, $field))->setParameter($field, $value);
                    }
                    return $qb;
                };
            }
            if ($fieldType == 'Tetranz\Select2EntityBundle\Form\Type\Select2EntityType') {
                // automatically set route and remote parameters
                $field['field_options']['remote_route'] = 'cube_custom_fields_ajax';
                $field['field_options']['remote_params']['fieldId'] = $name;
            }
            if (isset($field['field_options'])) {
                $options = array_merge($options, $field['field_options']);
            }
            // apply options override
            // TODO: find better solution here! --> remove multiple-option if not set currently
            if (!array_key_exists('multiple', $options) && array_key_exists('multiple', $overrideOptions)) {
                unset($overrideOptions['multiple']);
            }
            
            $options = array_replace_recursive($options, $overrideOptions);
            if (array_key_exists('multiple', $options));
            // add field to form
            $form->add($name, $fieldType, $options);
            // add model transformer for entity type fields
            if (EntityMapper::isEntityField($field['type'])) {
                //$form->get($name)->addModelTransformer( new \CubeTools\CubeCustomFieldsBundle\EntityHelper\EntityCustomFieldTransformer($this->em)) ;
            }
        }
    }
}
