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
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://andrewkandels.com/contain
 */

namespace Contain\Entity;

use Contain\Exception\InvalidArgumentException;
use Iterator;
use Traversable;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use RuntimeException;

/**
 * Setting Entity (auto-generated by the Contain module)
 *
 * This instance should not be edited directly. Edit the definition file instead 
 * and recompile.
 */
class Setting implements \Contain\Entity\EntityInterface, \Iterator
{
    /** @var Contain\Entity\Property\Type\StringType */
    protected $name;

    /** @var Contain\Entity\Property\Type\MixedType */
    protected $value;

    /** @var array */
    protected $_types = array();

    /** @var integer */
    protected $_iterator = 0;

    /** @var array */
    protected $_extendedProperties = array();

    /** @var array */
    protected $_dirty = array();

    /**
     * Constructor
     *
     * @param   array|Traversable               Properties
     * @return  $this
     */
    public function __construct($properties = null)
    {
        $this->_types['name'] = new \Contain\Entity\Property\Type\StringType();
        $this->_types['value'] = new \Contain\Entity\Property\Type\MixedType();
        $this->init();

        if ($properties) {
            $className = __CLASS__;
            if (is_object($properties) && $properties instanceof $className) {
                $this->fromArray($properties->toArray());
            } else {
                $this->fromArray($properties);
            }
        }
    }

    /**
     * Called when the Setting entity has been initialized. Commonly used to register
     * event hooks.
     *
     * @return  void
     */
    protected function init()
    {
        
    }


    /**
     * Fetches an extended property which can be set at anytime.
     *
     * @param   string                  Extended property name
     * @return  mixed
     */
    public function getExtendedProperty($name)
    {
        return isset($this->_extendedProperties[$name]) ? $this->_extendedProperties[$name] : null;
    }

    /**
     * Fetches all extended properties.
     *
     * @return  array
     */
    public function getExtendedProperties()
    {
        $result = array();

        foreach ($this->_extendedProperties as $name => $value) {
            $result[$name] = $this->getExtendedProperty($name);
        }

        return $result;
    }

    /**
     * Injects an extended property which can be set at anytime.
     *
     * @param   string                  Extended property name
     * @param   mixed                   Value to set
     * @return  $this
     */
    public function setExtendedProperty($name, $value)
    {
        $this->_extendedProperties[$name] = $value;

        return $this;
    }

    /**
     * Returns an array of the columns flagged as primary as the 
     * key(s) and the current values for the keys as the property
     * values.
     *
     * @return  mixed
     */
    public function primary()
    {
        return array(
        );
    }

    /**
     * Unsets one, some or all properties.
     *
     * @param   string|array|Traversable|null       Propert(y|ies)
     * @return  $this
     */
    public function clear($property = null)
    {
        if (!$property) {
            $property = $this->properties();
        }

        if (is_array($property) || $property instanceof Traversable) {
            foreach ($property as $name) {
                $this->clear($name);
            }

            return $this;
        }

        $method = 'set' . ucfirst($property);

        $this->$method($this->_types[$property]->getUnsetValue());

        return $this;
    }

    /**
     * Marks a changed property (or all properties by default) as clean, 
     * or unmodified.
     *
     * @param   string|array|Traversable|null       Propert(y|ies)
     * @return  $this
     */
    public function clean($property = null)
    {
        if (!$property) {
            $this->_dirty = array();

            return $this;
        }

        if (is_array($property) || $property instanceof Traversable) {
            foreach ($property as $name) {
                $this->clean($name);
            }
            return $this;
        }

        if (isset($this->_dirty[$property])) {
            unset($this->_dirty[$property]);
        }

        return $this;
    }

    /**
     * Returns dirty, modified properties.
     *
     * @return  array
     */
    public function dirty()
    {
        $result = array_keys($this->_dirty);

        return $result;
    }

    /**
     * Marks a property as dirty.
     *
     * @param   string                      Property name
     * @return  $this
     */
    public function markDirty($property)
    {
        if ($this->propertyExists($property)) {
            $this->_dirty[$property] = true;
        }

        return $this;
    }

    /**
     * Returns true if dirty, modified properties exist.
     *
     * @return  boolean
     */
    public function isDirty()
    {
        return (bool) $this->dirty();
    }

    /**
     * Gets the property type for a given property.
     *
     * @param   string          Property name
     * @return  Contain\Entity\Property\Type\TypeInterface
     */
    public function type($property)
    {
        return isset($this->_types[$property])
            ? $this->_types[$property]
            : null;
    }

    /**
     * Gets an array of all the entity's properties.
     *
     * @param   boolean             Include unset properties
     * @return  array
     */
    public function properties($includeUnset = false)
    {
        $result = array();
        if ($includeUnset || $this->hasName()) {
            $result[] = 'name';
        }
        if ($includeUnset || $this->hasValue()) {
            $result[] = 'value';
        }

        return $result;
    }
    
    /**
     * Returns true if a property exists for the entity (property does 
     * not need to be set however).
     *
     * @param   string                      Property name
     * @return  boolean
     */
    public function propertyExists($property)
    {
        return in_array($property, array('name', 'value'));
    }

    /**
     * Returns an array of all the entity properties.
     *
     * @param   boolean             Include unset properties
     * @return  array
     */
    public function toArray($includeUnset = false)
    {
        $properties = $this->properties($includeUnset);
        $result     = array();

        foreach ($properties as $property) {
            $method = 'get' . ucfirst($property);
            $value = $this->$method();
            if ($includeUnset || $this->_types[$property]->getUnsetValue() !== $value) {
                $result[$property] = $value;
            }
        }

        return $result;
    }

    /**
     * Hydrates entity properties from an array.
     *
     * @param   array|Traversable   Property key/value pairs
     * @param   boolean             Ignore errors
     * @param   boolean             Set undefined keys as extended properties
     * @return  $this
     */
    public function fromArray($properties, $ignoreErrors = true, $autoExtended = true)
    {
        if (!is_array($properties) && !$properties instanceof Traversable) {
            throw new InvalidArgumentException('$properties must be an array or an instance of Traversable.');
        }

        foreach ($properties as $key => $value) {
            if ($autoExtended && !$this->propertyExists($key)) {
                $this->setExtendedProperty($key, $value);
                continue;
            }

            switch ($key) {
                case 'name':
                    if ($ignoreErrors) {
                        try {
                            $this->setName($value);
                        } catch (\Exception $e) {
                            // ignored
                        }
                    } else {
                        $this->setName($value);
                    }
                    break;

                case 'value':
                    if ($ignoreErrors) {
                        try {
                            $this->setValue($value);
                        } catch (\Exception $e) {
                            // ignored
                        }
                    } else {
                        $this->setValue($value);
                    }
                    break;

                default:
                    if (!$ignoreErrors) {
                        throw new InvalidArgumentException("'$key' is not a valid property of "
                            . "the Setting entity."
                        );
                    }
                    break;
            }
        }

        return $this;
    }

    /**
     * Returns an array of all the entity properties
     * as an array of string-converted values (no objects).
     *
     * @param   Traversable|array|null              Properties
     * @param   boolean             Include unset properties
     * @return  array
     */
    public function export($includeProperties = null, $includeUnset = false)
    {
        $properties = $this->properties($includeUnset);
        $result     = array();

        if ($includeProperties) {
            if ($includeProperties instanceof Traversable) {
                $result = array();
                foreach ($includeProperties as $property) {
                    $result[] = $property;
                }
                $includeProperties = $result;
            } elseif (is_string($includeProperties)) {
                $includeProperties = array($includeProperties);
            } elseif (!is_array($includeProperties)) {
                throw new InvalidArgumentException('$includeProperties must be null, '
                    . 'a single property, or an array or Traversable object of '
                    . 'properties to export.'
                );
            }
        } else {
            $includeProperties = null;
        }

        foreach ($properties as $property) {
            if ($includeProperties && !in_array($property, $includeProperties)) {
                continue;
            }

            $method       = 'get' . ucfirst($property);
            $hasMethod    = 'has' . ucfirst($property);
            $unsetValue   = $this->_types[$property]->getUnsetValue();
            $defaultValue = $this->_types[$property]->getOption('defaultValue') ?: $unsetValue;
            $value        = $this->$hasMethod() ? $this->$method() : $defaultValue;
            if ($includeUnset || $unsetValue !== $value) {
                $result[$property] = $this->_types[$property]->export($value);
            }
        }

        return $result;
    }

    /**
     * Getter for the name property.
     *
     * @return  string
     */
    public function getName()
    {
        $value = $this->name;
        return $value;
    }

    /**
     * Setter for the name property.
     *
     * @param   string      Value to set
     * @return  $this
     */
    public function setName($value)
    {
        $value = $this->_types['name']->parse($value);
        if (!isset($this->_dirty['name']) && $value !== $this->name) {
            $this->_dirty['name'] = true;
        }
        $this->name = $value;
        return $this;
    }

    /**
     * Returns true if the name property has been set/hydrated.
     *
     * @return  boolean
     */
    public function hasName()
    {
        return $this->_types['name']->getUnsetValue() !== $this->name;
    }

    /**
     * Getter for the value property.
     *
     * @return  mixed
     */
    public function getValue()
    {
        $value = $this->value;
        return $value;
    }

    /**
     * Setter for the value property.
     *
     * @param   mixed      Value to set
     * @return  $this
     */
    public function setValue($value)
    {
        $value = $this->_types['value']->parse($value);
        if (!isset($this->_dirty['value']) && $value !== $this->value) {
            $this->_dirty['value'] = true;
        }
        $this->value = $value;
        return $this;
    }

    /**
     * Returns true if the value property has been set/hydrated.
     *
     * @return  boolean
     */
    public function hasValue()
    {
        return $this->_types['value']->getUnsetValue() !== $this->value;
    }

    /**
     * Rewinds the internal position counter (iterator).
     *
     * @return  void
     */
    public function rewind()
    {
        $this->_iterator = 0;
    }

    /**
     * Returns the property of the current iterator position.
     *
     * @return  Contain\Entity\Property
     */
    public function current()
    {
        $properties = $this->properties();
        $getter     = 'get' . ucfirst($properties[$this->_iterator]);
        return $this->$getter();
    }

    /**
     * Returns the current iterator property position.
     *
     * @return  integer
     */
    public function key()
    {
        $properties = $this->properties();
        return $properties[$this->_iterator];
    }

    /**
     * Advances the iterator to the next property.
     *
     * @return  void
     */
    public function next()
    {
        $this->_iterator++;
    }

    /**
     * Is the iterator in a valid position.
     *
     * @return  boolean
     */
    public function valid()
    {
        $properties = $this->properties();
        return isset($properties[$this->_iterator]);
    }

}
