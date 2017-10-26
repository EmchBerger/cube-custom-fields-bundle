<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
}
