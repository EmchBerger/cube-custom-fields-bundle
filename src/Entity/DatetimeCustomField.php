<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DatetimeCustomField extends CustomFieldBase
{
    /**
     * @var string
     *
     * @ORM\Column(type="datetime")
     */
    private $dateValue;

    /**
     * Set the value.
     *
     * @param string $value
     *
     * @return CustomFieldText $this
     */
    public function setValue(\DateTimeInterface $value)
    {
        $this->dateValue = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->dateValue;
    }

    public function __toString()
    {
        return $this->dateValue->format('d.m.Y h:i:s'); // TODO really fixed format?
    }
}
