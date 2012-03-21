<?php

use \Cumula\DataStore\YAML\YAML as YAML;

require_once 'Cumula/Test/Base.php';

class Test_YAML extends Test_BaseTest {

	public function testConstructor() {
		$ds = new YAML();
		$events = $ds->getEvents();
		$this->assertTrue($ds->eventIsRegistered('Load'));
		$this->assertTrue($ds->eventIsRegistered('Save'));
	}

	public function configuredInstance() {
		$ds = new YAML();
		$ds->setup(
			array('id', 'value'), 'id', 'testyaml',
			array(
				'source_directory' => CONFIGROOT,
				'filename' => uniqid("testyaml"))
			);
		$ds->connect();
		return $ds;
	}
	
	public function testCreate() {
		$ds = $this->configuredInstance();
		$obj = $ds->newObj(
			array('id' => 'one', 'value' => 'set')
			);
		$this->assertFalse($ds->recordExists($obj->id));
		$ds->create($obj);
		$this->assertTrue($ds->recordExists($obj->id));
		$this->assertEquals($obj, $ds->get($obj->id));
	}

	public function testLoadEvent() {
		$ds = $this->configuredInstance();
		$obj = $ds->newObj(
			array('id' => 'one', 'value' => 'set')
			);
		$ds->create($obj);
		$this->assertDispatches(
			$ds,
			'Load',
			function ($that) use ($ds, $obj) {
				$ds->get($obj->id);
			},
			array($obj)
			);
	}
	
	public function testSaveEvent() {
		$ds = $this->configuredInstance();
		$obj = $ds->newObj(
			array('id' => 'two', 'value' => 'set')
			);
		$callback = function ($that, $args) {
			$t_obj = $args[0];
			$that->assertEquals($t_obj->value, 'set');
			$t_obj->value = 'changed';
			return $t_obj;
		};
		$this->assertDispatches(
			$ds,
			'Save',
			function ($that) use ($ds, $obj) {
				$ds->create($obj);
			},
			array($obj),
			$callback
			);
		$new_obj = $ds->get($obj->id);
		$this->assertEquals($new_obj->value, 'changed');
	}

}
