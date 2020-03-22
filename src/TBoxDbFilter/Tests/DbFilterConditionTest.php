<?php
namespace TBoxDbFilter\Tests;

use TBoxDbFilter\Services\DbFilterCondition;


class DbFilterConditionTest
{
    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $_adapter;
            
    public function __construct() {
        $this->_adapter = new \Zend\Db\Adapter\Adapter(array(
            'driver' => 'pdo',
            'dsn' => 'mysql:dbname=stage_agency;charset=utf8;host=192.168.0.15',
            'database' => 'stage_agency',
            'username' => 'stage',
            'password' => 'oUpsIlaBakcEQtC3',
            'hostname' => '192.168.0.15',
        ));
    }
    
    public function run()
    {
        $classMethods = get_class_methods($this);
        foreach ($classMethods as $method) {
            if (strpos($method, 'test') === 0) {
                echo $method . ' ' . ($this->$method() ? 'OK' : 'Failed') . "\n";
            }
        }
    }
    
    public function testEqual()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('equal')
                          ->setProperty('Name')
                          ->setValue('Europe')
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $row = $result->current();
        return ( !empty($row) && $row['Id'] == 26 );
    }
    
    public function testEqualArray()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('equal')
                          ->setProperty('Name')
                          ->setValue(array('Europe', 'Asia'))
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) === 2 );
    }
    
    public function testNotEqual()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('notEqual')
                          ->setProperty('Name')
                          ->setValue('Europe')
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 9 );
    }
    
    public function testNotEqualArray()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('notEqual')
                          ->setProperty('Name')
                          ->setValue(array('Europe', 'Asia'))
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 8 );
    }
    
    public function testLessThan()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('lessThan')
                          ->setProperty('Id')
                          ->setValue(20)
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 2 );
    }
    
    public function testGreaterThan()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('greaterThan')
                          ->setProperty('Id')
                          ->setValue(30)
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 2 );
    }
    
    public function testLessThanOrEqual()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('lessThanOrEqual')
                          ->setProperty('Id')
                          ->setValue(25)
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 3 );
    }
    
    public function testGreaterThanOrEqual()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('greaterThanOrEqual')
                          ->setProperty('Id')
                          ->setValue(31)
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 2 );
    }
    
    public function testLike()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('like')
                          ->setProperty('Name')
                          ->setValue('*nia*')
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 2 );
    }
    
    public function testNotLike()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('notLike')
                          ->setProperty('Name')
                          ->setValue('*nia*')
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 8 );
    }
    
    public function testBetween()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('between')
                          ->setProperty('Timestamp')
                          ->setValue(array('2017-07-19 17:00:00', '2017-07-19 17:04:00'))
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 2 );
    }
    
    public function testExpression()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Name'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('expression')
                          ->setProperty('LOWER(Name) = ?')
                          ->setValue('europe')
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $row = $result->current();
        return ( !empty($row) && $row['Name'] == 'Europe' );
    }
    
    private function testStartsWith()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('startsWith')
                          ->setProperty('Name')
                          ->setValue('Eu')
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 2 );
    }
    
    private function testEndsWith()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('endsWith')
                          ->setProperty('Name')
                          ->setValue('nia')
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 2 );
    }

    private function testContains()
    {
        $sql = new \Zend\Db\Sql\Sql($this->_adapter);
        
        $select = new \Zend\Db\Sql\Select('areas');
        $select->columns(array('Id'));
        
        $dbFilterCondition = new DbFilterCondition();
        $dbFilterCondition->setType('contains')
                          ->setProperty('Name')
                          ->setValue('nia')
                          ->applyTo($select)
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return ( count($result) == 2 );
    }
}