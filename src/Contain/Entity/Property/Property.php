<?php
/**
 * Contain Project
 *
 * This source file is subject to the BSD license bundled with
 * this package in the LICENSE.txt file. It is also available
 * on the world-wide-web at http://www.opensource.org/licenses/bsd-license.php.
 * If you are unable to receive a copy of the license or have
 * questions concerning the terms, please send an email to
 * me@andrewkandels.com.
 *
 * @category    akandels
 * @package     contain
 * @author      Andrew Kandels (me@andrewkandels.com)
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://andrewkandels.com/contain
 */

namespace Contain\Entity\Property;

use ContainMapper\Cursor;
use Contain\Manager\TypeManager;
use Contain\Entity\EntityInterface;
use Traversable;

/**
 * Represents a single property for an entity.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Property
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $currentValue;

    /**
     * @var mixed
     */
    protected $persistedValue;

    /**
     * @var mixed
     */
    protected $unsetValue;

    /**
     * @var mixed
     */
    protected $emptyValue;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var Contain\Entity\EntityInterface
     */
    protected $parent;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Contain\Manager\TypeManager
     */
    protected $typeManager;

    /**
     * Injects the parent entity.
     *
     * @param   Contain\Entity\EntityInterface
     * @return  $this
     */
    public function setParent(EntityInterface $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Gets or sets a type manager.
     *
     * @param   Contain\Manager\TypeManager|null
     * @return  Contain\Manager\TypeManager
     */
    public function typeManager(TypeManager $manager = null)
    {
        if ($manager) {
            $this->typeManager = $manager;
        }

        if (!$this->typeManager) {
            $this->typeManager = new TypeManager();
        }

        return $this->typeManager;
    }

    /**
     * Hydrates the property from a syntax compatible with export().
     *
     * @param   array                           Serialized Options/Values
     * @return  $this
     */
    public function import(array $arr)
    {
        // unset the parent so we don't make any save()'s during hydration
        $parent = $this->parent;
        $this->parent = null;

        $this->options = array();
        if (!empty($arr['options'])) {
            $this->setOptions($arr['options']);
        }

        if (empty($arr['type'])) {
            throw new \Contain\Entity\Exception\InvalidArgumentException('import expects a type index');
        }
        $this->type = $arr['type'];

        if (empty($arr['name'])) {
            throw new \Contain\Entity\Exception\InvalidArgumentException('import expects a name index');
        }
        $this->name = $arr['name'];
        
        $this->unsetValue = array_key_exists('unsetValue', $arr)
            ? $arr['unsetValue']
            : $this->getType()->getUnsetValue();

        $this->emptyValue = array_key_exists('emptyValue', $arr)
            ? $arr['emptyValue']
            : $this->getType()->getEmptyValue();

        $this->currentValue = array_key_exists('currentValue', $arr)
            ? $arr['currentValue']
            : $this->unsetValue;

        if (array_key_exists('persistedValue', $arr)) {
            $this->persistedValue = $arr['persistedValue'];
        } else {
            $this->persistedValue = $this->currentValue;
        }

        // re-enable saves
        $this->parent = $parent;

        return $this;
    }

    /** 
     * Serializes the property for later hydration.
     *
     * @return  array
     */
    public function export()
    {
        return array(
            'name'           => $this->name,
            'options'        => $this->options,
            'currentValue'   => $this->currentValue,
            'persistedValue' => $this->persistedValue,
            'emptyValue'     => $this->emptyValue,
            'unsetValue'     => $this->unsetValue,
            'type'           => $this->type,
        );
    }

    /**
     * Sets the value for this property.
     *
     * @param   mixed                   Value
     * @return  $this
     */
    public function setValue($value)
    {
        $this->currentValue = $this->getType()->export($value);
        return $this->save();
    }

    /**
     * Sets the value for the index of the value, assuming the value itself is an 
     * array. This is really only used internally for updating event callbacks in 
     * lists and cursors of entites and should probably not be used outside of 
     * the Contain internals.
     *
     * @param   integer                 Index
     * @param   mixed                   export() value
     * @return  $this
     * @throws  Contain\Entity\Exception\InvalidArgumentException
     */
    public function setValueAtIndex($index, $value)
    {
        if (!isset($this->currentValue[$index])) {
            throw new \Contain\Entity\Exception\InvalidArgumentException('$index invalid for current value');
        } 

        $this->currentValue[$index] = $this->getType()->getType()->export($value);

        return $this->save();
    }

    /**
     * Gets the value for the index of the value, assuming the value itself is an 
     * array. This is really only used internally for updating event callbacks in 
     * lists and cursors of entites and should probably not be used outside of 
     * the Contain internals.
     *
     * @param   integer                 Index
     * @return  mixed
     * @throws  Contain\Entity\Exception\InvalidArgumentException
     */
    public function getValueAtIndex($index)
    {
        if (!isset($this->currentValue[$index])) {
            throw new \Contain\Entity\Exception\InvalidArgumentException('$index invalid for current value');
        } 

        return $this->currentValue[$index];
    }

    /**
     * Watches an entity for changes to its values, rolling those events
     * back up to the parent entity's property.
     *
     * @param   Contain\Entity\EntityInterface
     * @param   integer                                         Index
     * @return  $this
     */
    public function watch(EntityInterface $entity, $index = null)
    {
        $entity->setExtendedProperty('_parent', array(
            'parent' => $this->parent,
            'name'   => $this->name,
            'index'  => $index,
        ));

        // changing any value should persist back to be stored in the property's serialized version
        $entity->attach('change', function ($event) use ($index) {
            $entity = $event->getTarget();
            $e = $entity->getExtendedProperty('_parent');
            if ($index !== null) {
                $e['parent']->property($e['name'])->setValueAtIndex($index, $entity);
            } else {
                $e['parent']->property($e['name'])->setValue($entity);
            }
        }, -1000);

        // cleaning any sub-entity property should clean the property's serialized version
        $entity->attach('clean', function ($event) use ($index) {
            $entity = $event->getTarget();
            $e = $entity->getExtendedProperty('_parent');
            if ($index !== null) {
                $e['parent']->property($e['name'])->cleanAtIndex($index);
            } else {
                $e['parent']->property($e['name'])->cleanAtIndex($event->getParam('name'));
            }
        }, -1000);

        // dirtying any sub-entity property should dirty the property's serialized version
        $entity->attach('dirty', function ($event) use ($index) {
            $entity = $event->getTarget();
            $e = $entity->getExtendedProperty('_parent');
            if ($index !== null) {
                $e['parent']->property($e['name'])->setDirtyAtIndex($index);
            } else {
                $e['parent']->property($e['name'])->setDirtyAtIndex($event->getParam('name'));
            }
        }, -1000);

        return $this;
    }

    /** 
     * getValue() for entity properties, which must always return an actual
     * entity that can be acted upon with events to send back change actions.
     *
     * @return  Contain\Entity\EntityInterface
     */
    public function getEntityValue()
    {
        $type = $this->getType();

        // if unset, an empty one
        $value = $type->parse($this->persistedValue);
        if (!$value instanceof EntityInterface) {
            $value = $type->parse($this->emptyValue);
        }

        $value->clean();

        // update the dirty states by setting current properties
        $value->clear()->fromArray($this->currentValue ?: array());

        $this->watch($value);

        return $value;
    }

    /**
     * getValue() for lists of entity types, which slow-hydrate entities from a 
     * cursor. Changes to those entities should cycle back to the parent property.
     *
     * @return  ContainMapper\Cursor|array
     */
    public function getListEntityValue()
    {
        $value        = $this->getType()->parse($this->currentValue) ?: array();
        $propertyName = $this->name;
        $parent       = $this->parent;
    
        if ($value instanceof Cursor) {
            $value->getEventManager()->attach('hydrate', function ($event) use ($parent, $propertyName) {
                $entity = $event->getTarget();
                $parent->property($propertyName)->watch($entity, $event->getParam('index'));
            }, -1000);
        }

        return $value;
    }

    /**
     * getValue() for lists which needs to watch any entities it spawns for changes to 
     * propogate back to the parent property.
     *
     * @return  array
     */
    public function getListValue()
    {
        $value = $this->getType()->parse($this->currentValue) ?: array();

        if ($this->getType()->getOption('type') == 'entity') {
            foreach ($value as $index => $entity) {
                $this->watch($entity, $index);
            }
        }

        return $value; 
    }

    /**
     * Gets the value for this property.
     *
     * @return  mixed
     */
    public function getValue()
    {
        $type = $this->getType();

        if ($type instanceof Type\EntityType) {
            return $this->getEntityValue();
        }

        if ($type instanceof Type\ListEntityType) {
            return $this->getListEntityValue();
        }

        if ($type instanceof Type\ListType) {
            return $this->getListValue();
        }

        return $type->parse($this->currentValue);
    }

    /**
     * Returns true if the property has an unset value.
     *
     * @return  boolean
     */
    public function isUnset()
    {
        return $this->currentValue === $this->unsetValue;
    }

    /**
     * Returns true if the property has an empty value.
     *
     * @return  boolean
     */
    public function isEmpty()
    {
        return $this->currentValue === $this->emptyValue;
    }

    /**
     * Clears a property, setting it to an unset state.
     *
     * @return  $this
     */
    public function clear()
    {
        $this->currentValue = $this->unsetValue;
        return $this->save();
    }

    /**
     * Sets a property to its empty value.
     *
     * @return  $this
     */
    public function setEmpty()
    {
        $this->currentValue = $this->emptyValue;
        return $this->save();
    }

    /**
     * Sets a property dirty at a specified index of a list property.
     *
     * @param   integer                 Index
     * @return  $this
     */
    public function setDirtyAtIndex($index)
    {
        if (!is_array($this->persistedValue)) {
            $this->persistedValue = array();
        }

        $this->persistedValue[$index] = $this->getValue()->type($index)->getDirtyValue();

        return $this->save();
    }

    /**
     * Sets a property as dirty, which is tracked by the persisted value not equaling 
     * the current value of the property, which is ensured by making the persisted value
     * something one-of-a-kind.
     *
     * @return  $this
     */
    public function setDirty()
    {
        $this->persistedValue = $this->getType()->getDirtyValue();
        return $this->save();
    }

    /**
     * Cleans a property at a specified index of a list property.
     *
     * @return  $this
     */
    public function cleanAtIndex($index)
    {
        if (!is_array($this->persistedValue)) {
            $this->persistedValue = array();
        }

        if (!isset($this->currentValue[$index])) {
            unset($this->persistedValue[$index]);
            return $this;
        }

        $this->persistedValue[$index] = $this->currentValue[$index];

        return $this->save();
    }

    /**
     * Marks the current value as having been persisted for the sake of
     * dirty tracking.
     *
     * @return  $this
     */
    public function clean()
    {
        $this->persistedValue = $this->currentValue;
        return $this->save();
    }

    /**
     * Exports a serializable version of the current value.
     *
     * @return  mixed
     */
    public function getExport()
    {
        return $this->currentValue;
    }

    /**
     * Returns true if the current value of the property differs from its
     * last persisted value.
     *
     * @return  boolean
     */
    public function isDirty()
    {
        return $this->currentValue !== $this->persistedValue;
    }

    /**
     * Returns the value of this property when it was last persisted.
     *
     * @return  mixed
     */
    public function getPersistedValue()
    {
        return $this->getType()->parse($this->persistedValue);
    }

    /**
     * Returns the type object which defines how the data type
     * behaves.
     *
     * @return Contain\Entity\Property\Type\AbstractType
     */
    public function getType()
    {
        return $this->typeManager()->type(
            $this->type, 
            $this->options
        );
    }

    /**
     * Returns the alias of the current type as set in the define().
     *
     * @return  string
     */
    public function getTypeAlias()
    {
        return $this->type;
    }

    /**
     * Set entity options.
     *
     * @param   array|Traversable       Option name/value pairs
     * @return  $this
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new \Contain\Entity\Exception\InvalidArgumentException(
                '$options must be an instance of Traversable or an array.'
            );
        }

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this->save();
    }

    /**
     * Sets the value for an entity property's option.
     *
     * @param   string              Option name
     * @param   mixed               Option value
     * @return  $this
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this->save();
    }

    /**
     * Retrieves entity property's options as an array.
     *
     * @return  array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Retrieves entity property's property by name.
     *
     * @param   string              Option name
     * @return  array|null
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Gets the name of the property this object currently represents.
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the parent entity.
     *
     * @return  Contain\Entity\EntityInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Persists changes back to the parent's property array.
     *
     * @return  $this
     */
    public function save()
    {
        if ($this->parent) {
            $this->parent->saveProperty($this);
        }

        return $this;
    }
}
