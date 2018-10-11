<?php

namespace Tests\CubeTools\CubeCustomFieldsBundle\EntityHelper;

use CubeTools\CubeCustomFieldsBundle\EntityHelper\EntityMapper;
use PHPUnit\Framework\TestCase;

class EntityMapperTest extends TestCase
{
    public function testGetCustomFieldClass()
    {
        $ftTt = 'Symfony\Component\Form\Extension\Core\Type\TextType';
        $cfTt = EntityMapper::getCustomFieldClass($ftTt);
        $this->assertSame('CubeTools\CubeCustomFieldsBundle\Entity\TextCustomField', $cfTt, $ftTt);

        $ftTa = 'Symfony\Component\Form\Extension\Core\Type\TextareaType';
        $cfTa = EntityMapper::getCustomFieldClass($ftTa);
        $this->assertSame('CubeTools\CubeCustomFieldsBundle\Entity\TextareaCustomField', $cfTa, $ftTa);

        try { // currently accept two variants
            $invalid = EntityMapper::getCustomFieldClass('not known form class');
            $this->assertSame(null, $invalid);
        } catch (\LogicException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetFormClass()
    {
        $cfTa = 'CubeTools\CubeCustomFieldsBundle\Entity\TextareaCustomField';
        $ftTa = EntityMapper::getFormClass($cfTa);
        $this->assertSame('FOS\CKEditorBundle\Form\Type\CKEditorType', $ftTa, $cfTa);

        try { // currently accept two variants
            $invalid = EntityMapper::getFormClass('not known custom field class');
            $this->assertSame(null, $invalid);
        } catch (\LogicException $e) {
            $this->assertTrue(true);
        }
    }

    public function testIsEntityField()
    {
        $ftEt = 'Symfony\Bridge\Doctrine\Form\Type\EntityType';
        $this->assertTrue(EntityMapper::isEntityField($ftEt), 'inValue: '.$ftEt);

        $ftS2 = 'Tetranz\Select2EntityBundle\Form\Type\Select2EntityType';
        $this->assertTrue(EntityMapper::isEntityField($ftS2), 'inValue: '.$ftS2);

        $ftAny = 'any string';
        $this->assertFalse(EntityMapper::isEntityField($ftAny), 'inValue: '.$ftAny);
    }

    public static function assertSame($expected, $actual, $inValue = '')
    {
        // function is for getting nice message
        if ($inValue) {
            $inValue = 'inValue: '.$inValue;
        }

        return parent::assertSame($expected, $actual, $inValue);
    }
}
