<?php
namespace Cumula\Base;

abstract class Template extends Component {
	
	protected $_overrides_dir;
	protected $_overrides;
	protected $_files;
	protected $_full_dir;
	
	public function __construct() {
		parent::__construct();
		
		$this->_overrides_dir = 'files';
		$this->_files = array();
		$this->addEvent('GatherFileOverrides');
		$this->_full_dir = $this->rootDirectory().DIRECTORY_SEPARATOR.$this->_overrides_dir.DIRECTORY_SEPARATOR;
	}
	
	public function startup() {
		parent::startup();
		
		$components = A('ComponentManager')->getEnabledComponents();
		
		foreach($components as $component) {
			A($component)->bind('RenderFile', array($this, 'handleFileOverrides'));
		}
		
		$this->_files = $this->recurseDirectory($this->rootDirectory().DIRECTORY_SEPARATOR.$this->_overrides_dir);
		
		$this->_overrides = array();

		$overrides = &$this->_overrides;
		$this->dispatch('GatherFileOverrides', array(), function($new_overrides) use(&$overrides) {
			if($new_overrides) {
				if(is_callable($new_overrides))
					$overrides[] = $new_overrides;
				else if(is_array($new_overrides) && !empty($new_overrides))
					$overrides = array_merge($overrides, $new_overrides);
			}
		});
		$this->_overrides[] = function($file_name) {
			return 'Override'.$file_name;
		};
		
		$dir = $this->rootDirectory();
	}
	
	public function recurseDirectory($source_dir) {
		$files = array();
		foreach(glob($source_dir .'/*', GLOB_NOSORT) as $file) {
			$filename = basename($file);
			$parts = explode(DIRECTORY_SEPARATOR, $file);
			$lastDir = $parts[count($parts)-2];
			if (is_dir($file)) {
				$files = array_merge($files, $this->recurseDirectory($file));
			} else {
				$files[] = $lastDir.DIRECTORY_SEPARATOR.$filename;
			}
		}
		return $files;
	}
	
	public function handleFileOverrides($event, $dispatcher, $file_name) {
		$parts = explode('\\', get_class($dispatcher));
		$class = $parts[count($parts)-1];
		foreach($this->_overrides as $override) {
			$output = $class.DIRECTORY_SEPARATOR.call_user_func_array($override, array(basename($file_name)));
			if(in_array($output, $this->_files)) {
				return $this->_full_dir.$output;
			}
		}
	}
	
	public function renderTemplate($args) {
		extract($args, EXTR_OVERWRITE);
		ob_start();
		include $this->_full_dir.'template.tpl.php';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public function get404File() {
		return $this->_full_dir."404.html";
	}

}