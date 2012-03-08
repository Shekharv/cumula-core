<?php
namespace Cumula\Components\CommandLine;

use \A as A;
use \Cumula\Component\BaseComponent as BaseComponent;

class CommandLine extends BaseComponent {
	public function startup() {
		A('Router')->bind('GatherRoutes', 
			array(
				'>' => array($this, 'main'),
				'>help' => array(
					'callback' => array($this, 'help'),
					'help' => 'Get Cumula Help',
				),
				'>info' => array(
					'callback' => array($this, 'info'),
					'help' => 'Get info on the installed version of Cumula.',
				),
				'>setup' => array(
					'callback' => array($this, 'cumulaSetup'),
					'help' => 'Setup the Cumula Install.'
				)
			)
		);
	}
	
	public function main() {
		$this->render("Welcome to Cumula!");
	}
	
	public function help() {
		$output = "Cumula Help\n\nAvailable Commands\n";
		foreach(A('Router')->getRoutes() as $route) {
			$config = A('Router')->getRouteConfig($route);
			if(isset($config['help']))
				$output .= str_replace(">", "", $route)."\t".$config['help']."\n";
		}
		$this->render($output);
	}
	
	public function info() {
		$this->render("Welcome to Cumula.\nVersion ".CUMULAVERSION);
	}
	
	public function cumulaSetup() {
		fwrite(STDOUT, "Setting Up Cumula...");
		sleep(5);
		fwrite(STDOUT, "Done!");
	}
}