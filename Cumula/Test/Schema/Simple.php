<?php

use Cumula\SimpleSchema as SimpleSchema;

require_once 'base/Test.php';
require_once 'interfaces/CumulaSchema.interface.php';
require_once 'classes/BaseSchema.class.php';
require_once 'classes/SimpleSchema.class.php';

/**
 * Test of the SimpleSchema Class
 * @package Cumula
 * @subpackage Core
 **/
class Test_SimpleSchema extends Test_BaseTest {
    /**
     * Store the Schema object
     * @var SimpleSchema
     */
    private $schema;

    /**
     * Name used in the setUp of the SimpleSchema object
     * @var string
     */
    private $name;

    /**
     * setUp
     * @param void
     * @return void
     **/
    public function setUp() {
        $this->name = uniqid();
				$this->fields = array(
					'key' => 'string',
					'value' => 'text',
				);
				$this->id = 'key';
        $this->schema = new SimpleSchema($this->fields, $this->id, $this->name);
    } // end function setUp

    /**
     * Test the Constructor of the SimpleSchema class
     * @param void
     * @return void
     * @group all
     **/
    public function testConstructor() {
        $this->assertEquals($this->schema->getName(), $this->name);
        $this->assertEquals($this->schema->getIdField(), $this->id);
        $this->assertEquals($this->schema->getFields(), $this->fields);
    } // end function testConstructor

} // end class Test_SimpleSchema extends Test_BaseTest
