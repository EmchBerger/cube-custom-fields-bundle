<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CubeTools\CubeCustomFieldsBundle\Entity\GeneralCustomFieldRepository")
 */
class TextareaCustomField extends CustomFieldBase
{
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $textValue;

    /**
     * Set the value.
     *
     * @param string $value
     *
     * @return TextareaCustomField $this
     */
    public function setValue($value)
    {
        $this->textValue = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->textValue;
    }

    public function __toString()
    {
        return $this->textValue;
    }

    public static function getStorageFieldName()
    {
        return 'textValue';
    }
}
