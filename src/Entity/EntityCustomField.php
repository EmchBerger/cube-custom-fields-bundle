<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class EntityCustomField extends CustomFieldBase
{
    /**
     * @var string
     *
     * @ORM\Column(type="json_array")
     */
    private $entityValue;

    /**
     * Set the value.
     *
     * @param entity or array collection of entities
     *
     * @return EntityCustomField $this
     */
    public function setValue($value = null)
    {
        if ($value) {
            if (is_array($value) || $value instanceof \ArrayAccess) {
                // this is the case if multiple = true
                $saveValue = array();
                $saveClass = null;
                foreach ($value as $val) {
                    $saveValue[] = $val->getId();
                    $saveClass = get_class($val); // only the last class type is stored. We assume that it's the same anyway
                }
            } else {
                // this is the case if multiple = false
                $saveValue = $value->getId();
                $saveClass = get_class($value);
            }
            $this->entityValue = array(
                'entityClass' => $saveClass,
                'entityId' => $saveValue,
            );
        } else {
            $this->entityValue = null;
        }
        return $this;
    }

    public function getValue()
    {
        return $this->getEntityData();
    }

    public function __toString()
    {
        $entity = $this->getEntity();
        if ($entity) {
            return $entity->__toString();
        } else {
            return '';
        }
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
     * 
     * @global type $kernel
     * @return type either returns an entity or an array collection of entities
     */
    private function getEntityData()
    {
        if ($this->entityValue && $this->entityValue['entityClass']) {
            // TODO: find a better way to retrieve the entity manager
            global $kernel;
            $em = $kernel->getContainer()->getDoctrine_Orm_DefaultEntityManagerService();
            if (is_array($this->entityValue) && is_array($this->entityValue['entityId'])) {
                // multiple
                $entityData = $em->getRepository($this->entityValue['entityClass'])->findById($this->entityValue['entityId']); // in this case, $this->entityValue['entityId'] contains an array of entity IDs
            } else {
                // single
                $entityData = $em->getRepository($this->entityValue['entityClass'])->findOneById($this->entityValue['entityId']);
            }
            return $entityData;
        } else {
            return null;
        }
    }
}
