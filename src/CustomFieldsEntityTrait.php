<?php

namespace CubeTools\CubeCustomFieldsBundle;

use CubeTools\CubeCustomFieldsBundle\EntityHelper\CustomFieldsCollection;
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
     * @var ArrayCollection(BaseCustomField)
     *
     * @ORM\ManyToMany(targetEntity="CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase", indexBy="fieldId", cascade="all", orphanRemoval=true)
     * @ORM\JoinTable(inverseJoinColumns={@ORM\JoinColumn(unique=true)})
     * @var CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase[]
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

    public function hasCustomField($customField)
    {
        return $this->customFields->contains($customField);
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

    public function addCustomField($customField)
    {
        $this->customFields[$customField->getFieldId()] = $customField;
    }

    public function getCustomField($fieldId)
    {
        if (!isset($this->customFields[$fieldId])) {
            // TODO: here we need to check whether the fieldId is available for the entity at all (based on the configuration)
            return null;
        }

        return $this->customFields[$fieldId]->getValue();
    }

    /**
     * Gets a single field from the customFields ArrayCollection
     *
     * @param type $name
     * @return type
     */
    public function __get($name)
    {
        return $this->getCustomField($name);
    }

    /**
     * Sets a single field to the customFields ArrayCollection
     *
     * @param type $value
     */
    public function __set($name, $value)
    {
        // create CustomFieldCollection
        $customFields = new CustomFieldsCollection($this->customFields);
        // get the corresponding field for $name
        $customField = $customFields->get($name);
        // set the $value for the field
        $customField->setValue($value);
        // save the changed (or added) field back into the collection
        $customFields->set($name, $customField);
        // save the full list of CustomFieldBase entities as ArrayCollection back to the customFields variable of the main entity
        $this->setCustomFields($customFields->toArrayCollection());
    }

    /**
     * Persists all customFields to the EntityManager
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function persistCustomFields(\Doctrine\ORM\EntityManager $em)
    {
        foreach ($this->customFields as $customField) {
            $em->persist($customField);
        }
    }
}
