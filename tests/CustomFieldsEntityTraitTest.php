<?php

namespace Tests\CubeTools\CubeCustomFieldsBundle;

class CustomFieldsEntityTraitTest extends EntityHelper\CustomFieldsTestBase
{
    public function setUp()
    {
        $this->setTestConfig();
    }

    public function testGetSetCustomFields()
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
