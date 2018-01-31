<?php

namespace CubeTools\CubeCustomFieldsBundle;

use CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase;
use CubeTools\CubeCustomFieldsBundle\EntityHelper\CustomFieldsGetSet;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * To use in Entities to allow to add custom fields.
 *
 */
trait CustomFieldsEntityTrait
{
    /**
     * Custom fields linked to this entity.
     *
     * @var ArrayCollection of CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase
     *
     * @ORM\ManyToMany(targetEntity="CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase", indexBy="fieldId", cascade="all", orphanRemoval=true)
     * @ORM\JoinTable(inverseJoinColumns={@ORM\JoinColumn(unique=true)})
     *
     * ManyToMany+JoinTable with unique (= OneToMany) because the owning side can not be on the CustomFields table.
     * It is not inversed, since it would not work.
     */
    private $customFields;

    public function __construct()
    {
        $this->initCustomFields();
    }

    /**
     * To be called in the constructor only. Initialises the customFields.
     */
    protected function initCustomFields()
    {
        $this->customFields = new ArrayCollection();
    }

    /**
     * Get custom field entities which have a value set.
     *
     * @deprecated since version 1.3.5 use {@see getNonemptyCustomFields()} instead, it tells clearer what happens
     *
     * @return BaseCustomField[]
     */
    public function getCustomFields()
    {
        @trigger_error(__METHOD__.' is deprecated, use getNonemptyCustomFields() instead', E_USER_DEPRECATED);

        return $this->getNonemptyCustomFields();
    }

    /**
     * Get custom field entities which have a value set.
     *
     * @return BaseCustomField[]
     */
    public function getNonemptyCustomFields()
    {
        if (!$this->customFields) {
            $this->initCustomFields();
        }
        return $this->customFields;
    }

    /**
     * @deprecated since version 1.3.5, is probably unused
     */
    public function hasCustomField($customField)
    {
        @trigger_error(__METHOD__.' is deprecated', E_USER_DEPRECATED);

        return $this->customFields->contains($customField);
    }

    /**
     * Set custom fields entities.
     *
     * @deprecated since version 1.3.5, is probably unused and dangerous
     *
     * @param ArrayCollection $customFields
     *
     * @return $this
     */
    public function setCustomFields(ArrayCollection $customFields)
    {
        @trigger_error(__METHOD__.' is deprecated', E_USER_DEPRECATED);

        $this->customFields = $customFields;

        return $this;
    }

    /**
     * @deprecated since version 1.3.5, is probably unused
     */
    public function addCustomField(CustomFieldBase $customField)
    {
        @trigger_error(__METHOD__.' is deprecated', E_USER_DEPRECATED);

        CustomFieldsGetSet::setField($this, $customField);
    }

    public function setCustomField($fieldId, $value)
    {
        CustomFieldsGetSet::setValue($this, $fieldId, $value);
    }

    public function getCustomField($fieldId)
    {
        return CustomFieldsGetSet::getValue($this, $fieldId);
    }

    /**
     * Gets a single field from the customFields ArrayCollection
     *
     * @param string $name
     *
     * @return any
     */
    public function __get($name)
    {
        return $this->getCustomField($name);
    }

    /**
     * Sets a single field to the customFields ArrayCollection
     *
     * @param string $name
     * @param any    $value
     */
    public function __set($name, $value)
    {
        $this->setCustomField($name, $value);
    }
}
