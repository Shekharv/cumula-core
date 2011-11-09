<?php

require_once 'base/Test.php';
require_once 'classes/BaseDataStore.class.php';
require_once 'libraries/YAMLDataStore/YAMLDataStore.class.php';

/**
 * YamlDataStore tests
 * @package Cumula
 * @subpackage Core
 **/
class Test_YamlDataStore extends Test_BaseTest {
	/**
	 * DataStore Variable
	 * @var YamlDataStore
	 */
	private $dataStore;

	/**
	 * Schema for the data store
	 * @var MockCumulaSchema
	 */
	private $schema;

	/**
	 * Store the config used to configure the DataStore
	 * @var array
	 */
	private $config;

	/**
	 * setUp
	 * @param void
	 * @return void
	 **/
	public function setUp() {
		parent::setUp();

		$this->config = array(
			'source_directory' => vfsStream::url('app/config/'),
			'filename' => 'YAMLConfig.yaml',
		);

		$this->schema = $this->getMock('Cumula\\CumulaSchema');
		$this->schema->expects($this->any())
			->method('getFields')
			->will($this->returnValue(array('key', 'value')));
		$this->schema->expects($this->any())
			->method('getIdField')
			->will($this->returnValue('key'));

		$this->dataStore = new \YAMLDataStore\YAMLDataStore($this->schema, $this->config);
	} // end function setUp

	/**
	 * Test the Constructor Method
	 * @param void
	 * @return void
	 * @group all
	 * @covers YAMLDataStore\YAMLDataStore::__construct
	 * @covers YAMLDataStore\YAMLDataStore::getSchema
	 **/
	public function testConstructor() {
		$schema = $this->dataStore->getSchema();
		$this->assertInstanceOf('Cumula\\CumulaSchema', $schema);
		$this->assertEquals($this->schema, $schema);
	} // end function testConstructor

	/**
	 * Test the connect method
	 * @param void
	 * @return void
	 * @group all
	 * @covers YAMLDataStore\YAMLDataStore::connect
	 * @covers YAMLDataStore\YAMLDataStore::_load
	 **/
	public function testConnect() {
		file_put_contents($this->getDataStoreFile(), 'Key: Value');
		$this->assertFileExists($this->getDataStoreFile());
		$this->dataStore->connect();
		$this->assertFileExists($this->getDataStoreFile());


		$this->config['filename'] = 'doesNotExist.yml';
		$datastore = new \YAMLDataStore\YAMLDataStore($this->schema, $this->config);
		$this->assertFileNotExists($this->getDataStoreFile());
		$datastore->connect();
		$this->assertFileNotExists($this->getDataStoreFile());
	} // end function testConnect

	/**
	 * Test the create method
	 * @param void
	 * @return void
	 * @group all
	 * @covers YAMLDataStore\YAMLDataStore::create
	 * @covers YAMLDataStore\YAMLDataStore::update
	 * @covers YAMLDataStore\YAMLDataStore::_save
	 * @covers YAMLDataStore\YAMLDataStore::_dataStoreFile
	 * @covers YAMLDataStore\YAMLDataStore::_createOrUpdate
	 * @dataProvider createDataProvider
	 **/
	public function testCreate($method) {
		$obj = $this->getData();
		$this->dataStore->$method($obj);

		$contents = file_get_contents($this->getDataStoreFile());
		$this->assertContains(sprintf("%s: %s\n", $obj->key, $obj->value), $contents);
	} // end function testCreate

	/**
	 * Test the createOrUpdate method
	 * @param void
	 * @return void
	 * @group all
	 * @covers YAMLDataStore\YAMLDataStore::createOrUpdate
	 **/
	public function testCreateOrUpdate() {
		$data1 = $this->getData();

		// Loop twice; DRY
		for ($i = 0; $i < 2; $i++) {
			// Set create or update the data
			$this->dataStore->createOrUpdate($data1);
			$contents = file_get_contents($this->getDataStoreFile());
			$this->assertContains(sprintf("%s: %s\n", $data1->key, $data1->value), $contents);

			// Modify the data and continue
			$_data = each($this->getData());
			$data1->key = $_data['value'];
		}
	} // end function testCreateOrUpdate

	/**
	 * Test the destroy method
	 * @param void
	 * @return void
	 * @group all
	 * @covers YAMLDataStore\YAMLDataStore::destroy
	 **/
	public function testDestroy() {
		$data1 = $this->getData();
		$data2 = $this->getData();

		// Make sure the test data isn't the same
		$this->assertNotEquals($data1, $data2);

		$this->dataStore->create($data1);
		$this->dataStore->create($data2);

		$deleteMe = $data1;
		for ($i = 0; $i < 2; $i++) {
			$this->dataStore->destroy($deleteMe);
			$contents = file($this->getDataStoreFile());
			if (is_object($deleteMe)) {
				$this->assertFalse($this->dataStore->recordExists($deleteMe->key));
			}
			else {
				// Check the string 
				$foundFlag = FALSE;
				// search the contents for the key 
				foreach ($contents as $line) {
					if (!$foundFlag) {
						$foundFlag = (bool)stristr($line, $deleteMe);
					}
				}
				$this->assertTrue($foundFlag);
				$deleteMe = $data2->key;
			}
		}
	} // end function testDestroy

	/**
	 * Test the lastRowId method
	 * @param void
	 * @return void
	 * @group all
	 * @covers YAMLDataStore\YAMLDataStore::lastRowId
	 **/
	public function testLastRowId() {
		$max = rand(1, 20);
		for ($i = 0; $i < $max; $i++) {
			$this->dataStore->create($this->getData());
		}
		$this->assertEquals($max, $this->dataStore->lastRowId());
	} // end function testLastRowId

	/**
	 * Test the recordExists method
	 * @param void
	 * @return void
	 * @group all
	 * @covers YAMLDataStore\YAMLDataStore::recordExists
	 * @test
	 **/
	public function testRecordExists() {
		$data = $this->getData();

		$this->dataStore->create($data);
		$this->assertTrue($this->dataStore->recordExists($data->key));
	} // end function testRecordExists

	/**
	 * Test the Query method
	 * @param void
	 * @return void
	 * @group all
	 * @covers YAMLDataStore\YAMLDataStore::query
	 **/
	public function testQuery() {
		$data = $this->getData();
		$this->dataStore->create($data);
		$result = $this->dataStore->query(array('key' => $data->key));
		$this->assertEquals($data->value, $result[0][0]);
		$emptyResult = $this->dataStore->query('doesNotExist');
		$this->assertNull($emptyResult[0]);
	} // end function testQuery

	/**
	 * Data Provider for testCreate()
	 * @param void
	 * @return array
	 **/
	public function createDataProvider() {
		return array(
			'create' => array('create'),
			'update' => array('update'),
		);
	} // end function createDataProvider

	/**
	 * Get the full path to the dataStoreFile
	 * @param void
	 * @return string
	 **/
	private function getDataStoreFile() {
		return $this->config['source_directory'] . DIRECTORY_SEPARATOR . $this->config['filename'];
	} // end function getDataStoreFile

	/**
	 * Get test data
	 * @param void
	 * @return void
	 **/
	private function getData() {
		$obj = new stdClass();
		$obj->key = uniqid('key');
		$obj->value = uniqid('value');
		return $obj;
	} // end function getData

} // end class Test_YamlDataStore extends Test_BaseTest
