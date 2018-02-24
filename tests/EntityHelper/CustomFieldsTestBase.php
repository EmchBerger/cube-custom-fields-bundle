<?php

namespace Tests\CubeTools\CubeCustomFieldsBundle\EntityHelper;

use CubeTools\CubeCustomFieldsBundle\CustomFieldsEntityTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CustomFieldsTestBase extends TestCase
{
    const MOCK_ENTITY_CLASS = 'Mock_GetTestSetEntity';

    protected function setTestConfig()
    {
        global $kernel;
        if (!$kernel || 'M' === get_class($kernel)[0]) { // kernel is not set or is Mocked class
            // create mocked container in mocked kernel for UnsavedCustomField
            $config = array(self::MOCK_ENTITY_CLASS => array('notYetExisting' => array('type' => TextType::class)));
            $mockContainer = $this->getMockBuilder('dummy\Container')
                ->disableAutoload()
                ->setMethods(array('getParameter'))
                ->getMock();
            $mockContainer->expects($this->any())->method('getParameter')->will($this->returnValue($config));
            $mockKernel = $this->getMockBuilder('dummy\Kernel')
                ->disableAutoload()
                ->setMethods(array('getContainer'))
                ->getMock();
            $mockKernel
                ->expects($this->atLeastOnce()) // remove generating $kernel when not needed anymore
                ->method('getContainer')
                ->will($this->returnValue($mockContainer));
            $kernel = $mockKernel;
        }
    }

    protected function getMockEntity()
    {
        $entity = $this->getMockForTrait(CustomFieldsEntityTrait::class, array(), self::MOCK_ENTITY_CLASS);

        return $entity;
    }
}
