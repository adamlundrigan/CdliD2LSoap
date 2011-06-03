<?php
/**
 * Desire2Learn Web Serivces for Zend Framework
 * @author Adam Lundrigan <adamlundrigan@cdli.ca>
 * @author Thomas Hawkins <thawkins@mun.ca>
 *
 * $Id$
 *
 */

/**
 * PHPUnit test for D2LWS_Role_Model
 * @author Adam Lundrigan <adamlundrigan@cdli.ca>
 * @group ModelTests
 */
class D2LWS_Role_ModelTest extends GenericTestCase
{

    /**
     * Test that constructor works as expected
     */
    public function testCreateNewInstanceWithValidConstructorArgument()
    {
        $mock = $this->_createMockDataObject();
        $oRole = new D2LWS_Role_Model($mock);
        $this->assertEquals($mock, $oRole->getRawData());
    }

    /**
     * Test that we can create a "blank" instance of the model
     * by providing no argument to the constructor
     */
    public function testCreateNewInstanceWithoutConstructorArgument()
    {
        $oRole = new D2LWS_Role_Model();
        $this->assertNull($oRole->getRawData());
    }

    /**
     * Test that we can create a "blank" instance of the model
     * by providing NULL argument to the constructor
     */
    public function testCreateNewInstanceWithNullConstructorArgument()
    {
        $oRole = new D2LWS_Role_Model(NULL);
        $this->assertNull($oRole->getRawData());
    }

    /**
     * Test that we can create a "blank" instance of the model
     * by providing NULL argument to the constructor
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreateNewInstanceWithInvalidConstructorArgument()
    {
        $oRole = new D2LWS_Role_Model('shouldNotAcceptString');
    }

    /**
     * Test that setRoleID and getRoleID work as expected
     */
    public function testSetAndGetRoleID()
    {
        $testObj = $this->_createMockModel();
        $baseObj = $this->_createMockModel();

        // Set the Role ID
        $testObj->setRoleID(99);

        // Assert that a change occurred in the test object
        $this->assertNotEquals($testObj, $baseObj);

        // Assert that Role ID field was updated
        $this->assertEquals(99, $testObj->getRoleID());

        // Assert that no other return values were affected
        $this->_assertModelsSameExcept($testObj, $baseObj, 'RoleID');
    }

    /**
     * Test that setRoleName and getRoleName work as expected
     */
    public function testSetAndGetRoleName()
    {
        $testObj = $this->_createMockModel();
        $baseObj = $this->_createMockModel();

        // Set the Role Name
        $testObj->setRoleName('myTestRole');

        // Assert that a change occurred in the test object
        $this->assertNotEquals($testObj, $baseObj);

        // Assert that Role Name field was updated
        $this->assertEquals('myTestRole', $testObj->getRoleName());

        // Assert that no other return values were affected
        $this->_assertModelsSameExcept($testObj, $baseObj, 'RoleName');
    }

    /**
     * Test that setRoleCode and getRoleCode work as expected
     */
    public function testSetAndGetRoleCode()
    {
        $testObj = $this->_createMockModel();
        $baseObj = $this->_createMockModel();

        // Set the Role Code
        $testObj->setRoleCode('myRoleCode');

        // Assert that a change occurred in the test object
        $this->assertNotEquals($testObj, $baseObj);

        // Assert that Role Code field was updated
        $this->assertEquals('myRoleCode', $testObj->getRoleCode());

        // Assert that no other return values were affected
        $this->_assertModelsSameExcept($testObj, $baseObj, 'RoleCode');
    }

    /**
     * Defines the methods we should test
     * @var array
     */
    protected $_methodsToTest = array(
        'RoleID'=>array(
            'get'=>'getRoleID',
            'set'=>'setRoleID'
        ),
        'RoleName'=>array(
            'get'=>'getRoleName',
            'set'=>'setRoleName'
        ),
        'RoleCode'=>array(
            'get'=>'getRoleCode',
            'set'=>'setRoleCode'
        )
    );

    /**
     * Create an empty mock object
     */
    protected function _createMockDataObject()
    {
        $obj = new stdClass();
        
        $obj->RoleId = new stdClass();
        $obj->RoleId->Id = 0;
        $obj->RoleId->Source = "Desire2Learn";

        $obj->RoleName = new stdClass();
        $obj->RoleName->_ = '';

        $obj->Code = '';

        return $obj;
    }

    /**
     * Create mock model object
     */
    protected function _createMockModel()
    {
         return new D2LWS_Role_Model($this->_createMockDataObject());
    }
}