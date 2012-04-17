<?php
namespace Cumula\DataStore\YAML;

class Test_YAML extends \Cumula\Test\Base {

	public function testConstructor() {
		$config = array(
			"sourceDir" => CONFIGROOT,
			"filename" => uniqid("testyaml"),
			"fields" => array("id"=>true, "value"=>true),
			"idField" => "id"
			);
		$ds = new YAML($config);
		$events = $ds->getEvents();
		$this->assertTrue($ds->eventIsRegistered('Load'));
		$this->assertTrue($ds->eventIsRegistered('Save'));
	}

	public function configuredInstance() {
		$config = array(
			"sourceDir" => CONFIGROOT,
			"filename" => uniqid("testyaml"),
			"fields" => array("id"=>true, "value"=>true),
			"idField" => "id"
			);
		$ds = new YAML($config);
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
			null,
			$callback
			);
		$new_obj = $ds->get($obj->id);
		$this->assertEquals($new_obj->value, 'changed');
	}

}
