<?php

namespace CubeTools\CubeCustomFieldsBundle\EntityHelper;

/**
 * Maps form classes to customField classes
 */
class EntityMapper
{
    private static $map = array(
        'Symfony\Component\Form\Extension\Core\Type\TextType' => 'CubeTools\CubeCustomFieldsBundle\Entity\TextCustomField',
        'FOS\CKEditorBundle\Form\Type\CKEditorType' => 'CubeTools\CubeCustomFieldsBundle\Entity\TextareaCustomField',
        'Symfony\Component\Form\Extension\Core\Type\TextareaType' => 'CubeTools\CubeCustomFieldsBundle\Entity\TextareaCustomField', // we have two mapping for TextareaCustomField. the second (default textarea) is used only in "getCustomFieldClass"
        'Symfony\Component\Form\Extension\Core\Type\DateTimeType' => 'CubeTools\CubeCustomFieldsBundle\Entity\DatetimeCustomField',
        'Tetranz\Select2EntityBundle\Form\Type\Select2EntityType' => 'CubeTools\CubeCustomFieldsBundle\Entity\EntityCustomField',
        'Symfony\Bridge\Doctrine\Form\Type\EntityType' => 'CubeTools\CubeCustomFieldsBundle\Entity\EntityCustomField',
    );

    public static function getCustomFieldClass($formClass)
    {
        if (array_key_exists($formClass, self::$map)) {
            return self::$map[$formClass];
        } else {
            return null;
        }
    }

    public static function getFormClass($customFieldClass)
    {
        foreach (self::$map as $key => $elem) {
            if ($elem == $customFieldClass) {
                return $key;
            }
        }

        return null;
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
