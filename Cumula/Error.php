<?php

namespace Cumula;
use \Cumula\EventDispatcher as EventDispatcher;

class Error extends EventDispatcher {
	public static $levels = array(
		0                  => 'Error',
		E_ERROR            => 'Error',
		E_RECOVERABLE_ERROR => 'Error',
		E_WARNING          => 'Warning',
		E_PARSE            => 'Parsing Error',
		E_NOTICE           => 'Notice',
		E_CORE_ERROR       => 'Core Error',
		E_CORE_WARNING     => 'Core Warning',
		E_COMPILE_ERROR    => 'Compile Error',
		E_COMPILE_WARNING  => 'Compile Warning',
		E_USER_ERROR       => 'User Error',
		E_USER_WARNING     => 'User Warning',
		E_USER_NOTICE      => 'User Notice',
		E_STRICT           => 'Runtime Notice'
	);

	public static $exit_on = array(E_PARSE, E_ERROR, E_USER_ERROR, E_COMPILE_ERROR);
	
	public static $handled = false;
	
	public static function handleError($error, $message, $file, $line) {	
		$instance = static::instance();
		if($instance && count($instance->getEventListeners('error_encountered'))) {
			$instance->dispatch('error_encountered', array($error, $message, $file, $line));
			return;
		}	
		
		static::processError($error, $message, $file, $line);
		if ($error AND in_array($error, static::$exit_on)) {
			static::$handled = true;
			exit;
		}
	}
	
	public static function handleException($e) {
		$instance = static::instance();
		if($instance && count($instance->getEventListeners('error_encountered'))) {
			$instance->dispatch('error_encountered', array($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine()));
			return;
		}	
		
		static::processError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
		if ($e->getCode() AND in_array($e->getCode(), static::$exit_on)) {
			static::$handled = true;
			exit;
		}
	}
	
	public static function handleShutdown() {
		$last_error = error_get_last();
		if ($last_error AND in_array($last_error['type'], static::$exit_on) && !static::$handled) {
			$instance = static::instance();
			if($instance && count($instance->getEventListeners('error_encountered'))) {
				$instance->dispatch('error_encountered', array($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']));
			} else {
				while (ob_get_level() > 0)
				{
					ob_end_clean();
				}
				static::processError($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
			}
		}
	}
	
	public static function processError($error, $message, $file, $line) {
		$view = ROOT.DIRECTORY_SEPARATOR.'cumula'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'error.tpl.php';
		echo \Cumula\Renderer::renderFile($view, array('error' => static::$levels[$error], 
											'message' => $message, 
											'file' => $file, 
											'line' => $line));
	}
	
	//**********************************************
	//Instance Methods
	//**********************************************
	
	public function __construct() {
		parent::__construct();
		$this->addEvent('error_encountered');

		register_shutdown_function(array($this, 'handleShutdown'));
		set_exception_handler(array($this, 'handleException'));
		set_error_handler(array($this, 'handleError'));
	}
}
