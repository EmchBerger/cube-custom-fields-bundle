<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CubeTools\CubeCustomFieldsBundle\Entity\GeneralCustomFieldRepository")
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
     * @return DatetimeCustomField $this
     */
    public function setValue(\DateTimeInterface $value = null)
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
        if ($this->dateValue) {
            return $this->dateValue->format('d.m.Y h:i:s'); // TODO really fixed format?
        } else {
            return '';
        }
    }

    public static function getStorageFieldName()
    {
        return 'dateValue';
    }
}
