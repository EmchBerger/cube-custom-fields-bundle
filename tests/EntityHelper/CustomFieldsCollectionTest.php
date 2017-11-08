<?php

namespace Tests\CubeTools\CubeCustomFieldsBundle\EntityHelper;

use CubeTools\CubeCustomFieldsBundle\EntityHelper\CustomFieldsCollection;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CustomFieldsCollectionTest extends TestCase
{
    public function testGetSet()
    {
        $cfac = new CustomFieldsCollection();
        $this->assertCount(0, $cfac);

        $newEl = $cfac['notYetExisting'];
        $this->assertCount(0, $cfac, 'after getting');

        $cfac['notYetExisting'] = $newEl;
        $this->assertCount(0, $cfac, 'after setting nothing');

        $newEl->setValue('');
        $cfac['notYetExisting'] = $newEl;
        $this->assertCount(0, $cfac, 'after setting ""');

        $newEl->setValue('fkie1');
        $cfac['notYetExisting'] = $newEl;
        $this->assertCount(1, $cfac, 'after setting string');

        $getEl = $cfac['notYetExisting'];
        $this->assertNotSame(get_class($newEl), get_class($getEl), 'real class');
        // TODO check getEl: value, fieldId
        $cfac['newEl'] = $getEl;
        $this->assertCount(2, $cfac, 'after setting 2nd');

        $getNewEl = $cfac['newEl'];
        $this->assertSame('newEl', $getNewEl->getFieldId());
        $this->assertSame('notYetExisting', $cfac['notYetExisting']->getFieldId());

        $getNewEl->setValue('');
        $cfac['newEl'] = $getNewEl;
        $this->assertCount(1, $cfac, 'after setting new to ""');
    }

    public function testCreate()
    {
        $forTestData = new CustomFieldsCollection();
        $testData = array();
        $testData['x'] = $forTestData['x'];
        $testData['f'] = $forTestData['f'];

        $fromCol = new CustomFieldsCollection(new ArrayCollection($testData));
        $this->assertCount(2, $fromCol, 'from ArrayCollection');

        $testData['a'] = $forTestData['a']->setValue('123');
        $fromArr = new CustomFieldsCollection($testData);
        $this->assertCount(3, $fromArr, 'from array');
    }
}
