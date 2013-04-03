<?php
namespace Contain\Entity;

use Contain\Entity\AbstractEntity;
use Contain\Entity\Property\Property;

/**
 * Setting Entity (auto-generated by the Contain module)
 *
 * This instance should not be edited directly. Edit the definition file instead
 * and recompile.
 */
class Setting extends AbstractEntity
{
    protected $inputFilter = 'Contain\Entity\Filter\Setting';
    protected $messages = array();

    /**
     * Initializes the properties of this entity.
     *
     * @return  $this
     */
    public function init()
    {
        $this->define('name', 'string', array (
  'required' => true,
  'options' => 
  array (
    'label' => 'Name',
  ),
));
        $this->define('value', 'mixed', array (
  'options' => 
  array (
    'label' => 'Value',
  ),
));
            }


    /**
     * Accessor getter for the name property
     *
     * @return  See: Contain\Entity\Property\Type\StringType::getValue()
     */
    public function getName()
    {
        return $this->get('name');
    }

    /**
     * Accessor setter for the name property
     *
     * @param   See: Contain\Entity\Property\Type\StringType::parse()
     * @return  $this
     */
    public function setName($value)
    {
        return $this->set('name', $value);
    }

    /**
     * Accessor existence checker for the name property
     *
     * @return  boolean
     */
    public function hasName()
    {
        $property = $this->property('name');
        return !($property->isUnset() || $property->isEmpty());
    }

    /**
     * Accessor getter for the value property
     *
     * @return  See: Contain\Entity\Property\Type\MixedType::getValue()
     */
    public function getValue()
    {
        return $this->get('value');
    }

    /**
     * Accessor setter for the value property
     *
     * @param   See: Contain\Entity\Property\Type\MixedType::parse()
     * @return  $this
     */
    public function setValue($value)
    {
        return $this->set('value', $value);
    }

    /**
     * Accessor existence checker for the value property
     *
     * @return  boolean
     */
    public function hasValue()
    {
        $property = $this->property('value');
        return !($property->isUnset() || $property->isEmpty());
    }

}
