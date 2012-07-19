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

namespace Contain\Entity\Definition;

use Contain\Exception\InvalidArgumentException;
use Contain\Exception\RuntimeException;
use Contain\Entity\Property\Property;
use Iterator;

/**
 * Defines the behavior of an entity.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class AbstractDefinition implements Iterator
{
    /**
     * @var string
     */
    const ENTITY = 'entity';

    /**
     * @var string
     */
    const FILTER = 'filter';

    /**
     * @var array
     */
    protected $properties = array();

    /**
     * @var integer
     */
    protected $position = 0;

    /**
     * @var array
     */
    protected $options = array(
        'extended'  => false,
        'iteration' => true,
        'events'    => false,
    );

    /**
     * @var array
     */
    protected $import = array();

    /**
     * @var array
     */
    protected $registeredMethods = array();

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $targets = array(
        'entity' => '',
        'filter' => '',
    );

    /** 
     * @var string
     */
    protected $parentClass;

    /**
     * @var array
     */
    protected $implementors = array();

    /**
     * Constructor
     *
     * @return  $this
     */
    public function __construct()
    {
        $this->setUp();
    }

    /**
     * Called when an entity itself is instantiated. Used to register 
     * events.
     *
     * @return  $this
     */
    public function init()
    {
        foreach ($this->import as $definition) {
            $definition->init();
        }
    }

    /**
     * Registers a new property and returns the property object which can 
     * be invoked directly for additional options or passed as a third
     * argument.
     *
     * @param   string              Name of the property
     * @param   string              Data type (string, integer, etc.)
     * @return  Contain\Entity\Property
     */
    public function setProperty($property, $type = null)
    {
        if ($property instanceof Property) {
            $name = $property->getName();
        } else {
            $name = $property;

            if (!$type) {
                throw new InvalidArgumentException('$type must be specified if $property is not an object.');
            }
        }

        $this->removeProperty($name);

        if ($property instanceof Property) {
            $this->properties[] = $obj = $property;
        } else {
            $obj = new Property($property);
            $obj->setType($type);
            $this->properties[] = $obj;
        }

        return $obj;
    }

    /**
     * Finds a property object by its registered name.
     *
     * @param   string              Name of the property
     * @return  Contain\Entity\Property
     */
    public function getProperty($name)
    {
        foreach ($this->properties as $property) {
            if (!strcasecmp($property->getName(), $name)) {
                return $property;
            }
        }

        throw new InvalidArgumentException('$name is not a registered property');
    }

    /**
     * Checks to see if a property has been registered under a given name.
     *
     * @param   string              Name of the property
     * @return  boolean
     */
    public function hasProperty($name)
    {
        foreach ($this->properties as $property) {
            if (!strcasecmp($property->getName(), $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Unsets a property.
     *
     * @param   string              Name of the property
     * @return  $this
     */
    public function removeProperty($name)
    {
        foreach ($this->properties as $index => $property) {
            if (!strcasecmp($property->getName(), $name)) {
                unset($this->properties[$index]);
                $this->properties = array_merge(array(), $this->properties);
                break;
            }
        }

        return $this;
    }

    /**
     * Sets the target path for the compiler of a given item, of 
     * which the valid options include:
     *
     * 1) entity: The compiled entity object
     * 2) filter: The Zend\InputFilter\InputFilter implementation 
     *            for validation and data sanitizing.
     *
     * @param   string                  Target key (see above, e.g.: entity)
     * @param   string                  File system path
     * @return  $this
     */
    public function registerTarget($target, $path)
    {
        if (!isset($this->targets[$target])) {
            throw new InvalidArgumentException(
                "'$target' is not a valid key, valid options are: "
                . implode(', ', array_keys($this->targets)) . '.'
            );
        }

        $this->targets[$target] = $path;

        return $this;
    }

    /**
     * Returns an array of target keys and their paths (see setTarget()
     * for a list of valid keys and their definitions).
     *
     * @return  array
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * Gets the target path for a given target key (see setTarget()
     * for a list of valid keys and their definitions).
     *
     * @param   string                  Target key (e.g.: entity)
     * @return  string
     */
    public function getTarget($target)
    {
        if (!isset($this->targets[$target])) {
            throw new InvalidArgumentException(
                "'$target' is not a valid key, valid options are: "
                . implode(', ', array_keys($this->targets)) . '.'
            );
        }

        return $this->targets[$target];
    }

    /**
     * Rewinds the internal position counter (iterator).
     *
     * @return  void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Returns the property of the current iterator position.
     *
     * @return  Contain\Entity\Property
     */
    public function current()
    {
        return $this->properties[$this->position];
    }

    /**
     * Returns the current iterator property position.
     *
     * @return  integer
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Advances the iterator to the next property.
     *
     * @return  void
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Is the iterator in a valid position.
     *
     * @return  boolean
     */
    public function valid()
    {
        return isset($this->properties[$this->position]);
    }

    /**
     * Imports the properties and most behaviors from another definition.
     *
     * @param   Contain\Entity\Definition\AbstractDefinition
     * @return  $this
     */
    public function import($definition)
    {
        if (!$definition instanceof AbstractDefinition) {
            if (!is_string($definition)) {
                throw new InvalidArgumentException('$definition should be an '
                    . 'Contain\Entity\Definition\AbstractDefinition instance or a '
                    . 'reference a class that is.'
                );
            }

            if (strpos('\\', $definition) === false) {
                $def = 'Contain\Entity\Definition\\' . $definition;
            }

            if (!is_subclass_of($definition, 'Contain\Entity\Definition\AbstractDefinition')) {
                throw new InvalidArgumentException('$definition does not refer to a class that extends '
                    . 'Contain\Entity\Definition\AbstractDefinition.'
                );
            }

            $definition = new $definition();
        }

        foreach ($definition as $property) {
            $this->setProperty($property);
        }

        $this->import[] = $definition;

        return $this;
    }

    /**
     * Returns the definition classes this definition imports.
     *
     * @return  Contain\Entity\Definition\AbstractDefinition[]
     */
    public function getImports()
    {
        return $this->import;
    }

    /**
     * Registers a method of the definition class that should be compiled into the end entity.
     *
     * @param   string              Method name
     * @return  $this
     */
    public function registerMethod($method)
    {
        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException('$method is not a valid method of the definition class.');
        }

        $this->registeredMethods[] = $method;
        return $this;
    }

    /**
     * Returns all methods registered for the definition.
     * 
     * @return  array
     */
    public function getRegisteredMethods()
    {
        $result = array();

        foreach ($this->registeredMethods as $method) {
            $result[] = array($this, $method);
        }

        foreach ($this->import as $definition) {
            foreach ($definition->getRegisteredMethods() as $method) {
                $result[] = $method;
            }
        }

        return $result;
    }

    /**
     * Sets the entity name, defaults to the name of the definition class.
     *
     * @param   string                  Name
     * @return  $this
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * Gets the entity name, defaults to the name of the definition class.
     *
     * @return  string
     */
    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }

        return ($this->name = substr(__CLASS__, strrpos('\\', __CLASS__) + 1));
    }

    /** 
     * Sets the parent class the compiled entity will extend.
     *
     * @param   string
     * @return  $this
     */
    public function setParentClass($className)
    {
        $this->parentClass = $className;
        return $this;
    }

    /** 
     * Gets the parent class the compiled entity will extend.
     *
     * @return  string
     */
    public function getParentClass()
    {
        return $this->parentClass;
    }

    /** 
     * Registers an interface the compiled entity will implement.
     *
     * @param   array
     * @return  $this
     */
    public function registerImplementor($implementor)
    {
        $this->implementors[] = $implementor;
        return $this;
    }

    /** 
     * Sets the interfaces the compiled entity will implement.
     *
     * @param   array
     * @return  $this
     */
    public function setImplementors(array $implementors)
    {
        $this->implementors = $implementors;
        return $this;
    }

    /** 
     * Gets the interfaces the compiled entity will implement.
     *
     * @return  array
     */
    public function getImplementors()
    {
        $result = $this->implementors;

        if ($this->getOption('iteration') && !in_array('Iterator', $this->implementors)) {
            $result[] = '\Iterator';
        }

        return $result;
    }

    /**
     * Set entity level options.
     *
     * @param   array|Traversable       Option name/value pairs
     * @return  $this
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new InvalidArgumentException(
                '$options must be an instance of Traversable or an array.'
            );
        }

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * Sets the value for an entity's option.
     *
     * @param   string              Option name
     * @param   mixed               Option value
     * @return  $this
     */
    public function setOption($name, $value)
    {
        if (!isset($this->options[$name])) {
            throw new InvalidArgumentException(
                "'$name' is not a valid option. Valid options are: " 
                . implode(', ', array_keys($this->options)) . '.'
            );
        }

        $this->options[$name] = $value;

        return $this;
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
}