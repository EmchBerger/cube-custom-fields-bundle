<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CubeTools\CubeCustomFieldsBundle\Entity\GeneralCustomFieldRepository")
 */
class TextCustomField extends CustomFieldBase
{
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $strValue;

    /**
     * Set the value.
     *
     * @param string $value
     *
     * @return TextCustomField $this
     */
    public function setValue($value)
    {
        $this->strValue = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->strValue;
    }

    public function __toString()
    {
        return $this->strValue;
    }

    public static function getStorageFieldName()
    {
        return 'strValue';
    }
}
