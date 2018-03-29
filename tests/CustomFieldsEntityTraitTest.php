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
        $entity = $this->getMockEntity();
        $fields = $entity->getNonemptyCustomFields();
        $this->assertCount(0, $fields, 'initial empty');

        $unset = $entity->notYetExisting;
        $this->assertCount(0, $fields, 'getting does not modify');
        $this->assertNull($unset);

        $entity->notYetExisting = 'a239zu';
        $this->assertCount(1, $fields, 'after setting');
        $this->assertSame('a239zu', $entity->notYetExisting, 'get same');

        $entity->notYetExisting = '';
        $this->assertCount(0, $fields, 'back to 0');
        $this->assertNull($entity->notYetExisting);
    }

    public function testGetWrongCustomField()
    {
        $entity = $this->getMockEntity();

        $this->expectException(\LogicException::class);
        $entity->notExistingCustomField1;
    }

    public function testSetWrongCustomField()
    {
        $entity = $this->getMockEntity();

        $this->expectException(\LogicException::class);
        $entity->notExistingCustomField2 = 5;
    }
}
