<?php

namespace CubeTools\CubeCustomFieldsBundle\EntityHelper;

use CubeTools\CubeCustomFieldsBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Collection for CustomFieldBase entities.
 *
 * Creates new (unsaved) entities if required;
 */
class CustomFieldsArrayCollection extends ArrayCollection
{
    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     *
     * @param array|ArrayCollection $elements
     */
    public function __construct($elements = array())
    {
        if ($elements instanceof ArrayCollection) {
            $elements = $elements->toArray();
        }

        parent::__construct($elements);
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     * Returns a new UnsavedCustomField if the key does not exist.
     *
     * @param {@inheritdoc}
     *
     * @return {@inheritdoc}
     */
    public function get($key)
    {
        $entity = parent::get($key);

        if (!$entity) {
            $entity = new UnsavedCustomField();
            $entity->setFieldId($key);
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     * Special:
     *     * sets the FieldId to the key
     *     * removes entities with empty values
     *     * creates real classes from UnsavedCustomField
     *
     * @param {@inheritdoc}
     * @param CustomFieldBase $entity
     *
     * @return {@inheritdoc}
     */
    public function set($key, $entity)
    {
        if ($entity->isEmpty()) {
            // do not save empty entities
            $this->remove($key);

            return;
        }
        if ($entity instanceof UnsavedCustomField) {
            $entity = $this->createRealEntity($entity);
            $entity->setFieldId($key);
        } elseif ($entity->getFieldId() !== $key) {
            $entity = clone $entity;
            $entity->setFieldId($key);
        }
        parent::set($key, $entity);
    }

   /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     * Special:
     *     * returns true when the value could be here.
     *
     * @param $key {@inheritdoc}
     *
     * @return {@inheritdoc}
     */
    public function containsKey($key)
    {
        return is_string($key) || parent::containsKey($key);
    }

    /**
     * Creates a real entity from the unsaved one.
     *
     * @param \CubeTools\CubeCustomFieldsBundle\EntityHelper\UnsavedCustomField $tempEntity
     *
     * @return \CubeTools\CubeCustomFieldsBundle\Entity\*CustomField
     *
     * @throws \InvalidArgumentException
     */
    private function createRealEntity(UnsavedCustomField $tempEntity)
    {
        $value = $tempEntity->getValue();
        $type = gettype($value);
        /*
         * TODO Currently the entity type is based on the value type.
         * Looking up the fieldId in the config would be better. But how to get the config?
         */
        switch ($type) {
            case 'string':
                $entity = new Entity\TextCustomField();
                break;
            case 'object':
                $type = get_class($value); // for error message
                if ($value instanceof \DateTimeInterface || $value instanceof \DateTime) {
                    $entity = new Entity\DatetimeCustomField();
                    break;
                }
                // else go into default
            default:
                throw new \InvalidArgumentException('value of $entity has invalid type '.$type);
        }
        $entity->setValue($value);
        // do not set $entity->setFieldId($tempEntity->getFieldId), is set later anyway

        return $entity;
    }
}
