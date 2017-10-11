<?php

namespace Tests\CubeTools\CubeCustomFieldsBundle\Form;

use CubeTools\CubeCustomFieldsBundle\Form\CustomFieldsFormService;
use PHPUnit\Framework\TestCase;

class CustomFieldsFormServiceTest extends TestCase
{
    public function testEmptyConfig()
    {
        $service = new CustomFieldsFormService(array());
        $form = $this->getMockForm('SomeEntityClass');
        $form->expects($this->never())->method('add');

        $service->addCustomFields($form);
    }

    public function testEmptyConfigNoEntity()
    {
        $service = new CustomFieldsFormService(array());
        $form = $this->getMockForm();
        $form->expects($this->never())->method('add');

        $service->addCustomFields($form, 'AnEntityClass');
    }

    public function testEmptyConfigNothing()
    {
        $service = new CustomFieldsFormService(array());
        $form = $this->getMockForm();
        $form->expects($this->never())->method('add');

        $this->setExpectedException(\LogicException::class);
        $service->addCustomFields($form);
    }

    public function testEmptyConfigBoth()
    {
        $service = new CustomFieldsFormService(array());
        $form = $this->getMockForm('EntityClass1');
        $form->expects($this->never())->method('add');

        $this->setExpectedException(\LogicException::class);
        $service->addCustomFields($form, 'EntityClass1');
    }

    public function testBasicConfig()
    {
        $config = array(
            'EntityClassX' => array(
                'fieldOfX' => array(
                    'field_type' => 'text',
                ),
            ),
            'EntityClassB' => array(
                'fieldR' => array(
                    'field_type' => 'text',
                ),
                'fieldS' => array(
                    'field_type' => 'date',
                ),
            ),
        );
        $service = new CustomFieldsFormService($config);
        $form = $this->getMockForm('EntityClassB');
        $form->expects($this->exactly(2))->method('add');

        $service->addCustomFields($form);
    }

    private function getMockForm($formEntityClass = null)
    {
        $mock = $this->getMockBuilder('Symfony\Component\Form\FormBuilderInterface')
            ->getMock()
        ;
        $mockConfig = $this->getMockBuilder('dummy\FormBuilder')
            ->disableAutoload()
            ->setMethods(array('getOption'))
            ->getMock()
        ;
        $mockConfig->expects($this->any())->method('getOption')
            ->with($this->equalTo('class'))
            ->will($this->returnValue($formEntityClass))
        ;

        $mock->expects($this->any())->method('getFormConfig')
            ->will($this->returnValue($mockConfig))
        ;

        return $mock;
    }
}
