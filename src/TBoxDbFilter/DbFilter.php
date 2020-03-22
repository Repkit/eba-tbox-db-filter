<?php

namespace TBoxDbFilter;

/**
 * Description of DbFilter
 *
 */
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\AdapterInterface;
use TBoxDbFilter\Services\DbFilterCondition;

class DbFilter implements Interfaces\DbFilterInterface
{
    protected $Entity;
    protected $Options;
    protected $ObjectPrototype;
    protected $Settings;
    protected $DefinedFilters;
    protected $Adapter;
    

    public function __construct(AdapterInterface $Adapter,array $Settings = array(),array $DefinedFilters = array())
    {
        $this->Adapter = $Adapter;
        $this->Settings = $Settings;
        $this->DefinedFilters = $DefinedFilters;
    }

    public function getEntity()
    {
        return $this->Entity;
    }

    public function getOptions()
    {
        return $this->Options;
    }

    public function setEntity($Entity)
    {
        $this->Entity = $Entity;
    }

    public function setOptions($Options)
    {
        $this->Options = $Options;
    }

    public function getObjectPrototype()
    {
        return $this->ObjectPrototype;
    }

    public function setObjectPrototype($ObjectPrototype)
    {
        $this->ObjectPrototype = $ObjectPrototype;
    }

    public function getSettings()
    {
        return $this->Settings;
    }

    public function getDefinedFilters()
    {
        return $this->DefinedFilters;
    }

    public function setSettings($Settings)
    {
        $this->Settings = $Settings;
    }

    public function setDefinedFilters($DefinedFilters)
    {
        $this->DefinedFilters = $DefinedFilters;
    }

    public function getAdapter()
    {
        return $this->Adapter;
    }

    public function setAdapter($Adapter)
    {
        $this->Adapter = $Adapter;
    }

    public function Apply()
    {
        try
        {
            $statement = $this->createStatement();
            if (!isset($statement) || empty($statement))
            {
                $result = false;
            }
            else
            {
                $result = $this->execute($statement);
            }

            return $result;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    private function createStatement()
    {
        $statement = false;
        $options = $this->parseOptions($this->getOptions());
        if (isset($options) && !empty($options))
        {
            $adapter = $this->Adapter;
            if (isset($options['query']) && !empty($options['query']))
            {
                $dbQuery = $options['query'];
                $statement = $adapter->createStatement($dbQuery);
                $statement->prepare();
            }
            else
            {
                $select = new Select($options['from']);
                $where = $options['where'];
                $joins = $options['joins'];
                $sort = $options['sort'];
                $groupBy = array();
                foreach ($joins as $table => $join)
                {
                    $on = $join['condition'];
                    if (isset($join['groupBy']) && !empty($join['groupBy']))
                    {
                        $groupBy[] = $join['groupBy'];
                    }
                    $type = 'inner';
                    if (isset($join['type']) && !empty($join['type']))
                    {
                        $type = $join['type'];
                    }
                    $joinSelect = array();
                    if (isset($join['select']) && !empty($join['select']))
                    {
                        $joinSelect = $join['select'];
                    }
                    $select->join($table, new Expression($on),$joinSelect, $type);
                }
                
                $select = static::withWhere($select, $where);

                if (isset($groupBy) && !empty($groupBy))
                {
                    $select->group($groupBy);
                }
                unset($groupBy);
                if (isset($sort) && !empty($sort))
                {
                    $select->order($sort);
                }
                unset($sort);
                $select->quantifier(Select::QUANTIFIER_DISTINCT);
                $optionsData = $this->getOptions();
                if( isset($optionsData['extra-filters']) && !empty($optionsData['extra-filters']) )
                {
                    if( isset($optionsData['extra-filters']['limit'])  ){
                        $limit = intval($optionsData['extra-filters']['limit']);
                        if( !empty($limit) ){
                            $select->limit($limit);
                        }
                    } 
                    if( isset($optionsData['extra-filters']['offset'])  ){
                        $offset = intval($optionsData['extra-filters']['offset']);
                        if( isset($offset) ){
                            $select->offset($offset);
                        }
                    }
                }
                $sql = new Sql($adapter);
                $statement = $sql->prepareStatementForSqlObject($select);
            }
        }

        return $statement;
    }

    private function execute($Statement)
    {
        $resultSet = new ResultSet();
        if (isset($this->ObjectPrototype) && !empty($this->ObjectPrototype))
        {
            $resultSet->setArrayObjectPrototype(new $this->ObjectPrototype);
        }
        $resultSet->initialize($Statement->execute());

        return $resultSet;
    }

    private function parseOptions($Options)
    {
        $options = array();
        $settings = $this->getSettings();
        if (isset($settings['tables']) && !empty($settings['tables']) && isset($settings['tables'][$this->Entity]))
        {
            $dbTables = $settings['tables'];
            $dbJoins = array();
            if (isset($settings['joins']) && !empty($settings['joins']))
            {
                $dbJoins = $settings['joins'];
            }
            if (isset($Options['filter']) && !empty($Options['filter']))
            {
                $filterOptions = $Options['filter'];
                if (isset($filterOptions['key']) && !empty($filterOptions['key']))
                {
                    $filterKey = $filterOptions['key'];
                    $definedFilters = $this->getDefinedFilters();
                    if (isset($definedFilters) && isset($definedFilters[$this->Entity]) && isset($definedFilters[$this->Entity][$filterKey]))
                    {
                        $dbQuery = $definedFilters[$this->Entity][$filterKey];
                        if (isset($filterOptions['values']) && !empty($filterOptions['values']))
                        {
                            $filterValues = $filterOptions['values'];
                            foreach ($filterValues as $key => $value)
                            {
                                $valueToReplace = '{' . $key . '}';
                                if (is_array($value))
                                {
                                    $value = '(' . implode(",", $value) . ')';
                                }
                                $dbQuery = str_replace($valueToReplace, $value, $dbQuery);
                            }
                        }
                        $options['query'] = $dbQuery;
                    }
                }
                else
                {
                    if (!is_array($filterOptions))
                    {
                        $filterOptions = array($filterOptions);
                    }
                    $joins = array();
                    $where = array();
                    $sort = null;
                    $priorityTables = array();
                    $priorityTablesCount = array();
                    foreach ($filterOptions as $option)
                    {
                        if (!isset($option['name']) || empty($option['name']))
                        {
                            continue;
                        }
                        $name = $option['name'];
                        $properties = explode(".", $name);
                        $propertiesCount = count($properties) - 1;
                        $property = $properties[$propertiesCount];
                        if ($propertiesCount)
                        {
                            $propertyTable = $properties[($propertiesCount - 1)];
                        }
                        else
                        {
                            $propertyTable = $this->Entity;
                        }
                        if (isset($dbTables[$propertyTable]) && isset($dbTables[$propertyTable]['properties'][$property]))
                        {
                            $propertyName = $dbTables[$propertyTable]['name'] . '.' . $property;
                            if (isset($dbTables[$propertyTable]['properties'][$property]['priority']))
                            {
                                $priority = $dbTables[$propertyTable]['properties'][$property]['priority'];
                                $priorityTables[$propertyTable] = isset($priorityTables[$propertyTable]) ? ($priorityTables[$propertyTable] + $priority) : $priority;
                                $priorityTablesCount[$propertyTable] = isset($priorityTablesCount[$propertyTable]) ? ($priorityTablesCount[$propertyTable] + 1) : 1;
                                unset($priority);
                            }
                            $propertyPriotity = $tmpJoins = array();
                            $valid = true;
                            for ($index = 0; $index < $propertiesCount; $index++)
                            {
                                $table = $properties[$index];
                                if (!isset($joins[$table]))
                                {
                                    if (!isset($dbTables[$table]) || empty($dbTables[$table]))
                                    {
                                        $valid = false;
                                        break;
                                    }
                                    if ($index)
                                    {
                                        $joinTable = $properties[$index - 1];
                                    }
                                    else
                                    {
                                        $joinTable = $this->Entity;
                                    }
                                    if (isset($dbJoins[$joinTable]) && isset($dbJoins[$joinTable][$table]))
                                    {
                                        $join = $dbJoins[$joinTable][$table];
                                    }
                                    elseif (isset($dbJoins[$table]) && isset($dbJoins[$table][$joinTable]))
                                    {
                                        $join = $dbJoins[$table][$joinTable];
                                    }
                                    else
                                    {
                                        $valid = false;
                                        break;
                                    }
                                    $tmpJoins[$dbTables[$table]['name']] = $join;
                                }
                            }
                            if ($valid)
                            {
                                if (!empty($tmpJoins))
                                {
                                    $joins = array_merge($joins, $tmpJoins);
                                }
                                if (isset($option['direction']) && !empty($option['direction']))
                                {
                                    if ($option['direction'] == 'desc')
                                    {
                                        $sort = $propertyName . ' desc';
                                    }
                                    elseif ($option['direction'] == 'asc')
                                    {
                                        $sort = $propertyName . ' asc';
                                    }
                                }
                                else
                                {
                                    $option['name'] = $propertyName;
                                    $where[$name] = $option;
                                }
                                unset($valid);
                                unset($tmpJoins);
                            }
                        }
                    }
                    unset($properties);
                    unset($propertiesCount);
                    unset($filterOptions);
                    if (!empty($where) || isset($sort))
                    {
                        $valid = true;
                        if (!empty($priorityTables) && !isset($sort))
                        {
                            foreach ($priorityTables as $tableIdx => $priority)
                            {
                                if (($priority / $priorityTablesCount[$tableIdx]) < 50)
                                {
                                    $valid = false;
                                    break;
                                }
                            }
                        }
                        unset($priorityTables);
                        unset($priorityTablesCount);
                        if ($valid)
                        {
                            $options['where'] = $where;
                            $options['joins'] = $joins;
                            $options['sort'] = $sort;
                            $options['from'] = $dbTables[$this->Entity]['name'];
                        }
                        unset($dbTables);
                        unset($dbJoins);
                        unset($valid);
                    }
                }
            }
        }
        return $options;
    }

    public static function withWhere($Select, $Where, $TableName = null)
    {
        $where = $Where;
        $select = $Select;
        $sort = null;
        foreach ($where as $condition)
        {
            if( !isset($condition['name']) || empty($condition['name']) ){
                continue;
            }
            $property = $condition['name'];
            if(!empty($TableName)){
                $property = $TableName.'.'.$condition['name'];
            }

            if (isset($condition['type']) && !empty($condition['type']))
            {
                $type = $condition['type'];
            }
            elseif( isset($condition['direction']) && !empty($condition['direction']) )
            {
                if ($condition['direction'] == 'desc')
                {
                    $sort = $property . ' desc';
                }
                elseif ($condition['direction'] == 'asc')
                {
                    $sort = $property . ' asc';
                }
                continue;
            }
            else
            {
              $type = 'equal';  
            }

            if(!isset($condition['term']))
            {
                if($type != 'isNull' && $type != 'isNotNull'){
                    continue;
                }
                $value = null;
            }else{
                $value = $condition['term'];
            }
            $separator = null;
            if (isset($condition['separator']) && !empty($condition['separator']))
            {
                $separator = $condition['separator'];
            }
            $dbFilterCondition = new DbFilterCondition();
            $dbFilterCondition->setType($type)
                              ->setProperty($property)
                              ->setValue($value)
                              ->setSeparator($separator)
                              ->applyTo($select)
            ;
        }

        if( isset($sort) && !empty($sort) ){
            $select->order($sort);
        }

        return $select;
    }

}
