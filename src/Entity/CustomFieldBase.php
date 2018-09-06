<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Base class for custom field tables
 *
 * @ORM\Entity(repositoryClass="CubeTools\CubeCustomFieldsBundle\Entity\GeneralCustomFieldRepository")
 * @ORM\Table(name="custom_fields")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn("discr_type", type="string")
 * @ORM\HasLifecycleCallbacks()
 */
abstract class CustomFieldBase
{
    protected $strRepresentationOnFlushCreated;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank()
     */
    private $fieldId;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $strRepresentation;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id of the custom field.
     *
     * @param string $fieldId
     *
     * @return $this
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;

        return $this;
    }


    /**
     * Get the id of the custom field.
     *
     * @return string
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * Set the string representation of the custom field automatically during persisting
     *
     * @param string $str
     *
     * @return $this
     *
     */
    public function setStrRepresentation($str)
    {
        $this->strRepresentation = $str;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function storeStrRepresentation()
    {
        if (!$this->strRepresentationOnFlushCreated) {
            // we only want to store the string representation during update if it has not yet been done as part of the flush cycle of a related entity (refer to the EventListener)
            $this->setStrRepresentation($this->createStrRepresentation());
        }
    }

    public function storeStrRepresentationOnFlush($flushEntity)
    {
        $this->strRepresentationOnFlushCreated = true;
        $this->setStrRepresentation($this->createStrRepresentationOnFlush($flushEntity));
    }

    /**
     * Creates the string representation of the custom field. Should be overriden if required by the extending class.
     *
     * @return str
     */
    public function createStrRepresentation()
    {
        return $this->__toString();
    }

    /**
     * Creates the string representation of the custom field during flush of a possibly related entity. Should be overriden if required by the extending class.
     *
     * @return str
     */
    public function createStrRepresentationOnFlush($flushEntity)
    {
        return $this->__toString();
    }

    /**
     * Returns true when the entity (its value) is empty.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->getValue());
    }

    /**
     * Value as string, for showing in view pages,
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }

    /**
     * Get value of this custom field.
     *
     * @return many
     */
    abstract public function getValue();

    /**
     * Get name of DB storage field of this custom field.
     *
     * @return string
     */
    abstract public static function getStorageFieldName();
}
