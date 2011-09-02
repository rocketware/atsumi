<?php
class claparser_Standard implements claparser_Interface {
	public function parseCommand($command, $spec) {
		$return = array();
		$baseCommand = array_shift($command);
		$controller = array_shift($command);
		// Does the controller exist in the spec?
		if(in_array($controller, array_keys($spec))) {
			$controller = $spec[$controller];
		}
		$return['controller'] = $controller;
		// Method will always have to be determinded by methodlessRequest
		// Required info for working method our further up the stack
		$return['method'] = 'methodlessRequest';
		$return['args'] = $command;
		return $return; # fancy that
	}	
}
?>
