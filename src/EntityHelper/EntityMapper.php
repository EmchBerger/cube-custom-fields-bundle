<?php

namespace CubeTools\CubeCustomFieldsBundle\EntityHelper;

/**
 * Maps form classes to customField classes
 */
class EntityMapper
{
    /**
     * @var string[] custom field classes, with their base form class as key
     *
     * When a custom field class has multiple keys, the first one is used in @{see getFormClass()}
     */
    private static $map = array(
        'Symfony\Component\Form\Extension\Core\Type\TextType' => 'CubeTools\CubeCustomFieldsBundle\Entity\TextCustomField',
        'FOS\CKEditorBundle\Form\Type\CKEditorType' => 'CubeTools\CubeCustomFieldsBundle\Entity\TextareaCustomField',
        'Symfony\Component\Form\Extension\Core\Type\TextareaType' => 'CubeTools\CubeCustomFieldsBundle\Entity\TextareaCustomField', // we have two mapping for TextareaCustomField. the second (default textarea) is used only in "getCustomFieldClass"
        'Symfony\Component\Form\Extension\Core\Type\DateTimeType' => 'CubeTools\CubeCustomFieldsBundle\Entity\DatetimeCustomField',
        'Symfony\Component\Form\Extension\Core\Type\DateType' => 'CubeTools\CubeCustomFieldsBundle\Entity\DatetimeCustomField',
        'Tetranz\Select2EntityBundle\Form\Type\Select2EntityType' => 'CubeTools\CubeCustomFieldsBundle\Entity\EntityCustomField',
        'Symfony\Bridge\Doctrine\Form\Type\EntityType' => 'CubeTools\CubeCustomFieldsBundle\Entity\EntityCustomField',
    );

    /**
     * Get the custom field class for the form class
     *
     * @param string $formClass
     *
     * @return string custom field class
     */
    public static function getCustomFieldClass($formClass)
    {
        $tryFormClass = $formClass;
        while ($tryFormClass) {
            if (array_key_exists($tryFormClass, self::$map)) {
                return self::$map[$tryFormClass];
            } elseif (class_exists($tryFormClass)) {
                $tryFormClass = (new $tryFormClass())->getParent();
            } else {
                break; // done, unexisting class
            }
        }

        throw new \LogicException(sprintf('FormClass %s is not supported', $formClass));
    }

    /**
     * Gets the base form class for the custom field class.
     *
     * When there are sevaral base classes in @{see self::$map},
     * the first matching is returned.
     *
     * @param string $customFieldClass
     *
     * @return string form class for $customFieldClass
     */
    public static function getFormClass($customFieldClass)
    {
        $key = array_search($customFieldClass, self::$map, true);
        if (false !== $key) {
            return $key;
        } else {
            throw new \LogicException(sprintf('CustomFieldClass %s is not supported.', $customFieldClass));
        }
    }

    public static function isEntityField($formClass)
    {
        if ($formClass == 'Symfony\Bridge\Doctrine\Form\Type\EntityType' || $formClass == 'Tetranz\Select2EntityBundle\Form\Type\Select2EntityType') {
            return true;
        } else {
            return false;
        }
    }
}
