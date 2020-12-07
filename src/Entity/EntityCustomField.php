<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Util\ClassUtils;

/**
 * @ORM\Entity(repositoryClass="CubeTools\CubeCustomFieldsBundle\Entity\EntityCustomFieldRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class EntityCustomField extends CustomFieldBase
{
    /**
     * @var string
     *
     * @ORM\Column(type="json_array")
     */
    private $entityValue;

    private $entityData;

    /**
     * Set the value.
     *
     * @param any $value entity or array collection of entities
     *
     * @return EntityCustomField $this
     */
    public function setValue($value = null)
    {
        if ($value && !($value instanceof \Countable && 0 === count($value))) { // empty or empty collection
            // store into temporary variable
            $this->entityData = $value;

            // store into database format
            if (is_array($value) || $value instanceof \ArrayAccess) {
                // this is the case if multiple = true
                $saveValue = array();
                $saveClass = null;
                foreach ($value as $val) {
                    $saveValue[] = $val->getId();
                    $saveClass = ClassUtils::getClass($val); // only the last class type is stored. We assume that it's the same anyway
                }
            } else {
                // this is the case if multiple = false
                $saveValue = $value->getId();
                $saveClass = ClassUtils::getClass($value);
            }
            if ($saveClass) {
                $this->entityValue = array(
                    'entityClass' => $saveClass,
                    'entityId' => $saveValue,
                );
            } else {
                $this->entityValue = null;
            }
        } else {
            $this->entityValue = null;
            $this->entityData = null;
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
            // for some reason, implode does not work directly on the entity traversable
            $strArr = array();
            foreach ($entity as $e) {
                $strArr[] = $e->__toString();
            }
            return implode(', ', $strArr);
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
            // for some reason, implode does not work directly on the entity traversable
            $strArr = array();
            foreach ($entity as $e) {
                $strArr[] = $e->__toString();
            }
            return implode("\x1E", $strArr); // ASCII "record separator" character
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
        if (ClassUtils::getClass($entity) == ClassUtils::getClass($flushEntity) && $entity->getId() === $flushEntity->getId()) {
            return $flushEntity;
        } else {
            return $entity;
        }
    }

    /**
     * @return object|ArrayCollection either returns an entity or an array collection of entities
     */
    private function getEntityData()
    {
        return $this->entityData;
    }

    /**
     * Method to figure out, if data in database follow bundle
     * or no bundle architecture and compares it with doctrine data.
     * Fixes if data in database follow bundle architecture but doctrine doesn't.
     *
     * @param string                      $entityClass argument for $em->getRepository
     * @param \Doctrine\ORM\EntityManager $em          entity manager
     *
     * @return string filtered repository name
     */
    public function prepareRepositoryName($entityClass, $em)
    {
        $doctrineIsBundleArchitecture = (stripos($em->getMetadataFactory()->getAllMetadata()[0]->name, 'Bundle') !== false);
        $databaseIsBundleArchitecture = (stripos($entityClass, 'Bundle') !== false);
        $isCustomField = (stripos($entityClass, 'CubeCustomFieldsBundle') !== false);

        if (!$doctrineIsBundleArchitecture && $databaseIsBundleArchitecture && !$isCustomField) {
            $repositoryName = str_replace('Bundle', '', $entityClass);
        } else {
            $repositoryName = $entityClass;
        }

        return $repositoryName;
    }

    /**
     * LifeCycleCallback PostLoad, loading entities from DataBase.
     *
     * @ORM\PostLoad
     *
     * @param LifecycleEventArgs $event
     */
    public function loadEntitiesAtLoad(LifecycleEventArgs $event)
    {
        if ($this->entityValue && $this->entityValue['entityClass']) {
            $em = $event->getEntityManager();
            $repository = $em->getRepository($this->prepareRepositoryName($this->entityValue['entityClass'], $em));

            if (is_array($this->entityValue) && is_array($this->entityValue['entityId'])) {
                // multiple
                $entityData = $repository->findById($this->entityValue['entityId']); // in this case, $this->entityValue['entityId'] contains an array of entity IDs
            } else {
                // single
                $entityData = $repository->findOneById($this->entityValue['entityId']);
            }

            $this->entityData = $entityData;
        } else {
            $this->entityData = null;
        }
    }

    public static function getStorageFieldName()
    {
        return 'entityValue';
    }

    public function __clone()
    {
        if ($this->getId()) {
            // since doctrine makes special use of __clone, we need to make sure there is an id set already before cloning ourselves
            $this->id = null;
            $data = $this->getEntityData();
            if (is_array($data)) {
                $newData = new ArrayCollection($data);
            } elseif ($data instanceof ArrayCollection) {
                $newData = new ArrayCollection($data->toArray());
            } else {
                $newData = $data;
            }
            $this->setValue($newData);
        }
    }
}
