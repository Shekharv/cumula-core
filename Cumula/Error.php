<?php

namespace Cumula;

class Error extends EventDispatcher {
	public static $files = array();
	
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

	public static $exitOn = array(E_PARSE, E_ERROR, E_USER_ERROR, E_COMPILE_ERROR);
	
	public static $handled = false;
	
	public static function handleError($error, $message, $file, $line) {	
		$instance = static::instance();
		if($instance && count($instance->getEventListeners('ErrorEncountered'))) {
			$instance->dispatch('ErrorEncountered', array($error, $message, $file, $line));
			return;
		}	
		
		static::processError($error, $message, $file, $line);
		if ($error AND in_array($error, static::$exitOn)) {
			static::$handled = true;
			exit;
		}
	}
	
	public static function handleException($e) {
		$instance = static::instance();
		if($instance && count($instance->getEventListeners('ErrorEncountered'))) {
			$instance->dispatch('ErrorEncountered', array($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine()));
			return;
		}	
		
		static::processError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
		if ($e->getCode() AND in_array($e->getCode(), static::$exitOn)) {
			static::$handled = true;
			exit;
		}
	}
	
	public static function handleShutdown() {
		$last_error = error_get_last();
		if ($last_error AND in_array($last_error['type'], static::$exitOn) && !static::$handled) {
			$instance = static::instance();
			if($instance && count($instance->getEventListeners('ErrorEncountered'))) {
				$instance->dispatch('ErrorEncountered', array($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']));
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
											'line' => $line,
											'snippet' => static::getFileSnippet($file, $line)));
	}
	
	public static function getFileSnippet($filepath, $lineNum, $highlight = true, $padding = 5)
	{
		// We cache the entire file to reduce disk IO for multiple errors
		if ( ! isset(static::$files[$filepath]))
		{
			static::$files[$filepath] = file($filepath, FILE_IGNORE_NEW_LINES);
			array_unshift(static::$files[$filepath], '');
		}

		$start = $lineNum - $padding;
		if ($start < 0)
		{
			$start = 0;
		}

		$length = ($lineNum - $start) + $padding + 1;
		if (($start + $length) > count(static::$files[$filepath]) - 1)
		{
			$length = NULL;
		}

		$debugLines = array_slice(static::$files[$filepath], $start, $length, TRUE);

		if ($highlight)
		{
			$toReplace = array('<code>', '</code>', '<span style="color: #0000BB">&lt;?php&nbsp;', "\n");
			$replaceWith = array('', '', '<span style="color: #0000BB">', '');

			foreach ($debugLines as & $line)
			{
				$line = str_replace($toReplace, $replaceWith, highlight_string('<?php ' . $line, TRUE));
			}
		}
		return $debugLines;
	}
	
	//**********************************************
	//Instance Methods
	//**********************************************
	
	public function __construct() {
		parent::__construct();
		$this->addEvent('ErrorEncountered');
	}
}
