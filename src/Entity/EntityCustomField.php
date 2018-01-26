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
     * @param any $value entity or array collection of entities
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
        $entity = $this->getEntityData();
        if (is_array($entity) || $entity instanceof \ArrayAccess) {
            return implode(', ', $entity);
        } elseif ($entity) {
            return $entity->__toString();
        } else {
            return '';
        }
    }

    /**
     * Override the default string representation creator method
     *
     * @return string
     */
    public function createStrRepresentation()
    {
        $entity = $this->getEntityData();
        if (is_array($entity) || $entity instanceof \ArrayAccess) {
            return implode("\x1E", $entity); // ASCII "record separator" character
        } elseif ($entity) {
            return $entity->__toString();
        } else {
            return '';
        }
    }

    /**
     * Override the default string representation creator method for the onFlush event
     *
     * @return string
     */
    public function createStrRepresentationOnFlush($flushEntity)
    {
        $entity = $this->getEntityData();
        if (is_array($entity) || $entity instanceof \ArrayAccess) {
            $entityArr = array();
            foreach ($entity as $elem) {
                $entityArr[] = $this->getEntityOnFlush($elem, $flushEntity);
            }
            return implode("\x1E", $entityArr); // ASCII "record separator" character
        } elseif ($entity) {
            return $this->getEntityOnFlush($entity, $flushEntity)->__toString();
        } else {
            return '';
        }
    }

    /**
     * Checks if the two passed objects are "the same" but possibly in different states. Returns the second argument if they are the same, else the first
     * @param type $entity
     * @param type $flushEntity
     * @return type
     */
    private function getEntityOnFlush($entity, $flushEntity)
    {
        if (get_class($entity) == get_class($flushEntity) && $entity->getId() === $flushEntity->getId()) {
            return $flushEntity;
        } else {
            return $entity;
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
     * @global \Symfony\Component\HttpKernel\KernelInterface $kernel
     *
     * @return object|ArrayCollection either returns an entity or an array collection of entities
     */
    private function getEntityData()
    {
        if ($this->entityValue && $this->entityValue['entityClass']) {
            // TODO: find a better way to retrieve the entity manager
            global $kernel;
            $em = $kernel->getContainer()->get('doctrine')->getManager();
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

    public static function getStorageFieldName()
    {
        return 'entityValue';
    }
}
