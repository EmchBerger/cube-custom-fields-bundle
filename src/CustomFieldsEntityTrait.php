<?php

namespace CubeTools\CubeCustomFieldsBundle;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * To use in Entities to allow to add custom fields.
 */
trait CustomFieldsEntityTrait
{
    /**
     * Custom fields linked to this entity.
     *
     * @var ArrayCollection(BaseCustomField)
     *
     * @ORM\ManyToMany(targetEntity="CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase", indexBy="fieldId")
     * @ORM\JoinTable(inverseJoinColumns={@ORM\JoinColumn(unique=true)})
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
     * Get custom field entities.
     *
     * @return BaseCustomField[]
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }

    /**
     * Set custom fields entities.
     *
     * @param ArrayCollection $customFields
     *
     * @return $this
     */
    public function setCustomFields(ArrayCollection $customFields)
    {
        $this->customFields = $customFields;

        return $this;
    }
}
