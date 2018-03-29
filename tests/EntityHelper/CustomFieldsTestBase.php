<?php

namespace Tests\CubeTools\CubeCustomFieldsBundle\EntityHelper;

use CubeTools\CubeCustomFieldsBundle\CustomFieldsEntityTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CustomFieldsTestBase extends TestCase
{
    const MOCK_ENTITY_CLASS = 'Mock_GetTestSetEntity';

    public function tearDown()
    {
        $this->unsetTestConfig();
    }

    protected function setTestConfig()
    {
        global $kernel;
        if (!$kernel || 'M' === get_class($kernel)[0]) { // kernel is not set or is Mocked class
            // create mocked container in mocked kernel for UnsavedCustomField
            $config = array(
                'checkCorrectEntityFinding1' => array(
                    'aDateTimeField' => array('type' => 'invalid 1'),
                ),
                self::MOCK_ENTITY_CLASS => array(
                    'notYetExisting' => array('type' => TextType::class),
                    'aDateTimeField' => array('type' => DateTimeType::class),
                    'someEntityType' => array('type' => EntityType::class),
                ),
                'checkCorrectEntityFinding2' => array(
                    'aDateTimeField' => array('type' => 'also not valid'),
                ),
            );

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

    protected function unsetTestConfig()
    {
        global $kernel;
        static $mockNoKernel = null;

        if ($kernel && (is_null($mockNoKernel) || $kernel instanceof $mockNoKernel)) {
            if (is_null($mockNoKernel)) {
                $mockNoKernel = $this->getMockBuilder('dummy\NoKernel')->disableAutoLoad()->getMock();
            }
            $kernel = $mockNoKernel;
        }
    }

    protected function getMockEntity()
    {
        $entity = $this->getMockForTrait(CustomFieldsEntityTrait::class, array(), self::MOCK_ENTITY_CLASS);

        return $entity;
    }
}
