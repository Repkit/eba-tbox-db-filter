<?php
namespace TBoxDbFilter\Services;

use Zend\Db\Sql\Select;


class DbFilterCondition
{
    /**
     * @var string
     */
    private $_type = NULL;
    
    /**
     * @var string
     */
    private $_property = NULL;
    
    /**
     * @var int|bool|string|array
     */
    private $_value = NULL;
    
    /**
     * @var string
     */
    private $_separator = NULL;
    
    /**
     * @var Select
     */
    private $_select = NULL;
    
    
    public function __construct() {
        return $this;
    }

    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }
    
    public function setProperty($property)
    {
        $this->_property = $property;
        return $this;
    }
    
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }
    
    public function setSeparator($separator)
    {
        $this->_separator = $separator;
        return $this;
    }
    
    public function applyTo(Select $select)
    {
        $this->_select = $select;
        $this->_validateProperties();
        $fn = '_condition' . ucwords($this->_type);
        $this->$fn();
    }
    
    private function _validateProperties()
    {
        if ($this->_property === NULL || !is_string($this->_property)) {
            throw new \Exception('Invalid Property parameter');
        }
        /*if (!method_exists($this, '_condition' . ucwords($this->_type))) {
            throw new \Exception('Invalid Type parameter');
        }*/
    }
    
    private function _conditionEqual()
    {
        if (is_array($this->_value)) {
            $this->_select->where->in($this->_property, $this->_value);
            if ($this->_separator == 'AND') {
                $having = new \Zend\Db\Sql\Having();
                $count = count($this->_value);
                $having->expression('count(' . ' DISTINCT ' . $this->_property . ' ) >= ?', $count);
                $this->_select->having($having);
            }
        } else {
            $this->_select->where->equalTo($this->_property, $this->_value);
        }
    }
    
    private function _conditionNotEqual()
    {
        if (is_array($this->_value)) {
            $this->_select->where->notIn($this->_property, $this->_value);
        } else {
            $this->_select->where->notEqualTo($this->_property, $this->_value);
        }
    }
    
    private function _conditionLessThan()
    {
        $this->_select->where->lessThan($this->_property, $this->_value);
    }
    
    private function _conditionGreaterThan()
    {
        $this->_select->where->greaterThan($this->_property, $this->_value);
    }
    
    private function _conditionLessThanOrEqual()
    {
        $this->_select->where->lessThanOrEqualTo($this->_property, $this->_value);
    }
    
    private function _conditionGreaterThanOrEqual()
    {
        $this->_select->where->greaterThanOrEqualTo($this->_property, $this->_value);
    }
    
    private function _conditionLike()
    {
        $value = str_replace('*', '%',$this->_value);
        $this->_select->where->like($this->_property, $value);
    }
    
    private function _conditionNotLike()
    {
        $value = str_replace('*', '%',$this->_value);
        $this->_select->where->notLike($this->_property, $value);
    }
    
    private function _conditionBetween()
    {
        $value = $this->_value;
        if (is_array($value) && count($value) == 2) {
            $minValue = reset($value);
            $maxValue = end($value);
            $this->_select->where->between($this->_property, $minValue, $maxValue);
        }
    }
    
    private function _conditionExpression()
    {
        $this->_select->where->expression($this->_property, $this->_value);
    }
    
    private function _conditionStartsWith()
    {
        $this->_select->where->like($this->_property, $this->_value . '%');
    }
    
    private function _conditionEndsWith()
    {
        $this->_select->where->like($this->_property, '%' . $this->_value);
    }

    private function _conditionContains()
    {
        $this->_select->where->like($this->_property, '%' . $this->_value . '%');
    }

    private function _conditionIsNull()
    {
        $this->_select->where->isNull($this->_property, $this->_value);
    }

    private function _conditionIsNotNull()
    {
        $this->_select->where->isNotNull($this->_property, $this->_value);
    }

    public function __call($method, $arguments)
    {
       $this->_select->where->{$this->_type}($this->_property, $this->_value);
    }
}
