<?php

namespace Tests\CubeTools\CubeCustomFieldsBundle\EntityHelper;

use CubeTools\CubeCustomFieldsBundle\EntityHelper\CustomFieldsGetSet;
use CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase;
use Doctrine\Common\Collections\Collection;

class CustomFieldsGetSetTest extends CustomFieldsTestBase
{
    public function setUp()
    {
        $this->setTestConfig();
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
}
