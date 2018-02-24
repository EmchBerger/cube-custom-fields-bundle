<?php

namespace Tests\CubeTools\CubeCustomFieldsBundle\EntityHelper;

use CubeTools\CubeCustomFieldsBundle\CustomFieldsEntityTrait;
use PHPUnit\Framework\TestCase;

class CustomFieldsTestBase extends TestCase
{
    const MOCK_ENTITY_CLASS = 'Mock_GetTestSetEntity';

    protected function getMockEntity()
    {
        $entity = $this->getMockForTrait(CustomFieldsEntityTrait::class, array(), self::MOCK_ENTITY_CLASS);

        return $entity;
    }
}
