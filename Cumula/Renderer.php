<?php

namespace Cumula;

class Renderer {
	
	static public function renderFile($fileName, $args) {
		extract($args, EXTR_OVERWRITE);
		ob_start();
			include $fileName;
			$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	
	static public function renderJson($data) {
		return json_encode($data);
	}
	
	static public function renderXML($data) {
		//TODO: Implement XML serialization of objects
	}
	
	static public function renderJSONP($data, $callback) {
		return "$callback($data);";
	}
}