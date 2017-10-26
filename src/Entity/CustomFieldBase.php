<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Base class for custom field tables
 *
 * @ORM\Entity
 * @ORM\Table(name="custom_fields")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn("discr_type", type="string")
 */
abstract class CustomFieldBase
{
    protected $config;

    public function __construct()
    {
        // TODO: find a better way to retrieve the parameters from custom_fields.yml
        global $kernel;
        $this->config = $kernel->getContainer()->getParameter('cubetools.customfields.entities');
    }

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
     * Returns the type according to the configuration
     *
     * @return string
     */
    public function getType()
    {
        // traverse the config and return the type of the first matching element
        foreach ($this->config as $entity) {
            foreach ($entity as $fieldId => $conf) {
                if ($fieldId == $this->getFieldId()) {
                    return $conf['type'];
                }
            }
        }
        return null;
    }
}
