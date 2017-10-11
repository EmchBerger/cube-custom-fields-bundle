<?php

namespace CubeTools\CubeCustomFieldsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FieldsViewContoller extends Contoller
{
    public function valuesAction($entity)
    {
        // maybe check if entity is an expected one ( $entity instanceof CustomFieldsEntityInterface or ...)
        $entityClass = get_class($entity);
        $config = $this->getParameter('cubetools.customfields.entities');
        $entityConfig = isset($config[$entityClass]) ? $config[$entityClass] : array();
        $values = $entity->getCustomFields();

        $show = array();
        foreach ($entityConfig as $field => $fieldConfig) {
            $showField = array('id' => $field, 'usId' => ucfirst($field));
            if (isset($fieldConfig['something'])) {
                // set something
            }
            $show[$field] = $showField;
        }

        $this->render('CubeToolsCustomFields:FieldsView:values', array(
            'values' => $values,
            'show' => $show,
        ));
    }
}
