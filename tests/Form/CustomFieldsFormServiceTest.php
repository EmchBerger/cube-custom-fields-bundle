<?php

namespace Tests\CubeTools\CubeCustomFieldsBundle\Form;

use CubeTools\CubeCustomFieldsBundle\Form\CustomFieldsFormService;
use PHPUnit\Framework\TestCase;

class CustomFieldsFormServiceTest extends TestCase
{
    public function testEmptyConfig()
    {
        $service = $this->getFormService(array());
        $form = $this->getMockForm('SomeEntityClass');
        $form->expects($this->never())->method('add');

        $service->addCustomFields($form);
    }

    public function testEmptyConfigNoEntity()
    {
        $service = $this->getFormService(array());
        $form = $this->getMockForm();
        $form->expects($this->never())->method('add');

        $service->addCustomFields($form, 'AnEntityClass');
    }

    public function testEmptyConfigNothing()
    {
        $service = $this->getFormService(array());
        $form = $this->getMockForm();
        $form->expects($this->never())->method('add');

        $this->setExpectedException(\LogicException::class);
        $service->addCustomFields($form);
    }

    public function testEmptyConfigBoth()
    {
        $service = $this->getFormService(array());
        $form = $this->getMockForm('EntityClass1');
        $form->expects($this->never())->method('add');

        $this->setExpectedException(\LogicException::class);
        $service->addCustomFields($form, 'EntityClass2');
    }

    public function testBasicConfig()
    {
        $config = array(
            'EntityClassX' => array(
                'fieldOfX' => array(
                    'type' => '...\TextType',
                ),
            ),
            'EntityClassB' => array(
                'fieldR' => array(
                    'type' => '...\TextType',
                ),
                'fieldS' => array(
                    'type' => '...\DateType',
                ),
            ),
        );
        $service = $this->getFormService($config);
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
            ->with($this->equalTo('data_class'))
            ->will($this->returnValue($formEntityClass))
        ;

        $mock->expects($this->any())->method('getFormConfig')
            ->will($this->returnValue($mockConfig))
        ;

        return $mock;
    }

    private function getFormService(array $config)
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')->getMock();
        $mr = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')->getMock();
        $mr->expects($this->any())->method('getManager')->willReturn($em);

        return new CustomFieldsFormService($config, $mr);
    }
}
