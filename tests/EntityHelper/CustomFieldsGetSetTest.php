<?php

namespace Tests\CubeTools\CubeCustomFieldsBundle\EntityHelper;

use CubeTools\CubeCustomFieldsBundle\EntityHelper\CustomFieldsGetSet;
use CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CustomFieldsGetSetTest extends CustomFieldsTestBase
{
    public function setUp()
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

    public function testGetSet()
    {
        $entity = $this->getMockEntity();
        $cfac = $entity->getNonemptyCustomFields();
        $this->assertTrue($cfac instanceof Collection, 'matching class');

        $newEl = CustomFieldsGetSet::getField($entity, 'notYetExisting');
        $this->assertCount(0, $cfac, 'after getting');
        $this->assertSame(null, $newEl);

        CustomFieldsGetSet::setValue($entity, 'notYetExisting', null);
        $this->assertCount(0, $cfac, 'after setting nothing');

        CustomFieldsGetSet::setValue($entity, 'notYetExisting', 'fkie1');
        $this->assertCount(1, $cfac, 'after setting string');

        $getEl = CustomFieldsGetSet::getField($entity, 'notYetExisting');
        $this->assertTrue($getEl instanceof CustomFieldBase, 'matching class');
        $this->assertSame('fkie1', $getEl->getValue());

        $getEl = CustomFieldsGetSet::setField($entity, 'newEl', $getEl);
        $this->assertCount(2, $cfac, 'after setting 2nd');

        $getNewEl = CustomFieldsGetSet::getField($entity, 'newEl');
        $this->assertSame('newEl', $getNewEl->getFieldId());
        $this->assertSame('notYetExisting', $cfac['notYetExisting']->getFieldId());

        $getNewEl->setValue('');
        $getEl = CustomFieldsGetSet::setField($entity, 'newEl', $getNewEl);
        $this->assertCount(1, $cfac, 'after setting new to ""');
    }

    public function testCreate()
    {
        $forTestData = $this->getMockEntity();
        $testData = array();
        $testData['x'] = $forTestData->notYetExisting;

        $this->markTestIncomplete('test more of entity');
        $testData['f'] = $forTestData->anotherUnsetValue;

        $forTestData->a = 123;
        $this->assertCount(3, $fromArr, 'from array');
    }
}
