<?php

namespace Tests\CubeTools\CubeCustomFieldsBundle\Entity;

use CubeTools\CubeCustomFieldsBundle\Entity\EntityCustomField;
use PHPUnit\Framework\TestCase;

class MockMetadataFactory
{
    private $customFieldName;

    public function setCustomFieldName($customFieldName)
    {
        $this->customFieldName = $customFieldName;
    }

    public function getAllMetadata()
    {
        if (is_null($this->customFieldName)) {
            $outputArray = array();
        } else {
            $stdObject = new \stdClass();
            $stdObject->name = $this->customFieldName;
            $outputArray = array($stdObject);
        }

        return $outputArray;
    }
}

class MockEntityManagerMetadataFactory
{
    private $mockMetadataInstance;

    public function __construct()
    {
        $this->mockMetadataInstance = new MockMetadataFactory();
    }

    public function setCustomFieldName($customFieldName)
    {
        $this->mockMetadataInstance->setCustomFieldName($customFieldName);
    }

    public function getMetadataFactory()
    {
        return $this->mockMetadataInstance;
    }
}

class EntityCustomFieldTest extends TestCase
{
    protected $testEntity;

    protected $mockEntityManager;

    public function setUp()
    {
        $this->testEntity = new EntityCustomField();
        $this->mockEntityManager = new MockEntityManagerMetadataFactory();
    }

    public function testPrepareRepositoryName()
    {
        $this->mockEntityManager->setCustomFieldName('App\\Entity\\User');
        $this->assertEquals('App\\Entity\\User', $this->testEntity->prepareRepositoryName('AppBundle\\Entity\\User', $this->mockEntityManager));

        $this->mockEntityManager->setCustomFieldName('App\\Entity\\User');
        $this->assertEquals('App\\Entity\\User', $this->testEntity->prepareRepositoryName('App\\Entity\\User', $this->mockEntityManager));

        $this->mockEntityManager->setCustomFieldName('AppBundle\\Entity\\User');
        $this->assertEquals('AppBundle\\Entity\\User', $this->testEntity->prepareRepositoryName('AppBundle\\Entity\\User', $this->mockEntityManager));
    }
}
