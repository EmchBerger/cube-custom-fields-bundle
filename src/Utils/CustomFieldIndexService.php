<?php

namespace CubeTools\CubeCustomFieldsBundle\Utils;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\Form;

/**
 * This service allows to get required information from custom_fields table for building up index pages
 * of entities linked to custom fields.
 */
class CustomFieldIndexService
{
    private $configReader;

    public function __construct(ConfigReader $configReader)
    {
        $this->configReader = $configReader;
    }

    /**
     * Retrieves the values of the custom fields for a given entity according to a given form fields set (filterform)
     * Values are returned together with a raw parameter (boolean) indicating whether the raw filter should be applied during print out
     *
     * @param Form $filterform    Filter form containing all form fields which shall be available for filtering.
     * @param object $entity    An entity stored in the database
     *
     * @return array            Contains the values of all custom fields which are referenced in the entity for all types contained in filterform, together with 'raw' indicator
     */
    public function getIndexRows(Form $filterform, $entity)
    {
        $entityClass = ClassUtils::getClass($entity);
        $fieldConfig = $this->configReader->getConfigForEntity($entityClass);
        $indexRows = array();
        // iterate over filterform custom fields
        foreach ($filterform as $filterfield) {
            if ($filterfield->getConfig()->getOption('translation_domain') == 'custom_fields') {
                $fieldGetter = $filterfield->getName();
                // we can access the customField directly by its name (using the magic getter function in the custom field trait):
                $value = $entity->$fieldGetter;
                $raw = false;
                if (array_key_exists($fieldGetter, $fieldConfig) && $fieldConfig[$fieldGetter]['type'] == 'FOS\CKEditorBundle\Form\Type\CKEditorType') {
                    $raw = true;
                }
                if (is_object($value) && $value instanceof \DateTimeInterface) {
                    $value = $value->format('d.m.Y');
                }
                $indexRows[] = array(
                    'value' => $value,
                    'raw' => $raw,
                );
            }
        }

        return $indexRows;
    }

    /**
     * Retrieves the header data of the custom fields for a given form fields set (filterform)
     * Values are returned together with a raw parameter (boolean) indicating whether the raw filter should be applied during print out
     *
     * @param Form $filterform  Filter form containing all form fields which shall be available for filtering.
     * @param array $options    array containing additional option/value pairs which should be passed on to the header elements (e.g. for styling)
     *
     * @return array            Contains the header data of all custom fields which are referenced in the entity for all types contained in filterform
     */
    public function getHeader(Form $filterform, $options = array())
    {
        $header = array();
        foreach ($filterform as $filterfield) {
            if ($filterfield->getConfig()->getOption('translation_domain') == 'custom_fields') {
                $headerElem = array(
                    'name' => $filterfield->getName(),
                    'class' => $filterfield->getName() . 'Col',
                    'label'=> $filterfield->getConfig()->getOption('label'),
                );
                foreach ($options as $optionKey => $option) {
                    $headerElem[$optionKey] = $option;
                }
                $header[] = $headerElem;
            }
        }

        return $header;
    }

}
