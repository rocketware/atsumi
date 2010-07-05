<?php
/**
 * @version		0.90
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

/**
 * Advanced URI Parser for complex uri parsing based on uriparser_Gyokuro
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class uriparser_Kabusecha implements uriparser_Interface {
	/* CONSTANTS */
	/* PROPERTIES */
	/* CONSTRUCTOR & DESTRUCTOR */
	/* GET METHODS */
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Converts a URI into an array for parsing
	 * @access protected
	 * @param string $uri The uri to be converted
	 * @return array The URI as an array
	 */
	 public static function decompileUri($uri) {
		$parts = explode('/', str_replace('__', '/_', $uri));
		if(end($parts) == '') array_pop($parts);
		return $parts;
	}

	/**
	 * Tests weather a controller has given permission to allow a nested method
	 * TODO: PHP5.3+: This can not be completed until PHP 5.3.0 & name needs reducing in size?!
	 * @access protected
	 * @param unknown_type $controllerName
	 * @param unknown_type $methodArr
	 * @param unknown_type $newMethod
	 */
	protected static function ControllerGrantsPermissionToNestMethods($controllerName, $methodArr, $newMethod) {
		//	$controllerName::pageNesting()
		return true;
   	}

   	/**
   	 * Recursively searches a specification for the required controller
   	 * @static
   	 * @access public
   	 * @param array $specification The specification to search
   	 * @param string $controller The name of the controller being searched for
   	 * @param array $pathArr An array to store recursion data
   	 */
   	public static function searchSpec($specification, $controller, $pathArr = array()) {
   		foreach($specification as $path => $class) {
   			if(is_array($class)) {
   				$newPathArr = $pathArr;
   				$newPathArr[] = $path;

			$out = $this->searchSpec($class, $controller, $newPathArr);
				if(is_array($out)) return $out;
		}
		elseif($class == $controller) {
			// make sure we don't get blanks in the path arr
			if(!empty($path) && $path != '') $pathArr[] = $path;
			return $pathArr;
		}
			// didn't match
   		}
   	}

   	/**
   	 * Creates a uri for a given controller and method
   	 * @access public
   	 * @param array $specification The specification to use to create
   	 * @param string $controller The name of the controller to be used
   	 * @param string $method The name of the method to be used
   	 * @param array $args A one dimentional array of args to pass to the method
   	 */
	public function createUri($specification, $controller, $method, $args = array()) {
		$components = self::searchSpec($specification, $controller);

		// TODO: add args parser!!
		$path = (count($components) ? implode('/',$components).'/'. $method.'/' : '');
		return $path;
	}

	/**
	 * Parsers a uri using the given specification to determine the required controller, method and args
	 * @access public
	 * @param string $uri The uri to parse
	 * @param array $specfication The specification to parse against
	 */
	public function parseUri($uri, $spec) {
		/* strip get parameters if any */
		if(strpos($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));

		// Force the user to have an index controller
		if(!array_key_exists('', $spec))
			throw new Exception('Spec must contain an index controller');

		$parts = self::DecompileUri($uri);
		$trace = array(
			'spec'		=> array(),
			'stack'		=> array(),
			'path'		=> array(
				'raw'	=> $uri,
				'parts'	=> $parts,
				'eval'	=> $parts
			)
		);

		$specSection = $spec;
		$section = array();
		foreach($parts as $key => $current) {
			$section = end($trace['stack']);

			// See if we are dealing with a controller
			if(array_key_exists($current, $specSection)) {
				// If the current section is an array then move into the spec
				if(is_array($specSection[$current])) {
					$specSection = $specSection[$current];
					$current = '';
				}

				// If an index for that section does not exist, create a blank one
				if(!array_key_exists($current, $specSection))
					$specSection[$current] = '';

				// Check the class exists here because its seems to mess up on the method exists
				// if the class has not ready been autoloaded
				if(!class_exists($specSection[$current]))
					throw new Exception('Spec defines class that cannot be found.');

				// Add a controller to the stack trace
				$trace['stack'][] = array(
					'controller'	=> $specSection[$current],
					'method'		=> 'page_index',
					'args'			=> array()
				);

				// Add an evaluation trace for the current part
				$trace['path']['eval'][$key] = '[Controller]: "'.$current.'"';
				continue;
			}

			// See if we are dealing with a variable
			if(substr($current, 0, 1) == '_') {
				// If the current method is set as methodlessRequest then add the args differently
				if($section['method'] == 'methodlessRequest')
					array_push($section['args']['args'], substr($current, 1));
				else
					array_push($section['args'], substr($current, 1));

				// Update the stack trace
				$trace['stack'][count($trace['stack'])-1] = $section;

				// Add an evaluation trace for the current part
				$trace['path']['eval'][$key] = '[ Argument ]: "'.$current.'"';
				continue;
			}

			// Convert any dots in method names to double underscore
			$current = str_replace('.', '__', $current);
			$current = 'page_'.$current;
			if(method_exists($section['controller'], $current)) {
				$method = $current;
				$args = array();
			} else {
				$method = 'methodlessRequest';
				$args = array('method' => $current, 'args' => array());
			}

			// Nested method permisson check
			if($section['method'] != 'page_index' && $section['method'] != 'methodlessRequest') {
				// We are dealing with a nested method
			}

			// Add the new method with the last controller to the stack
			$trace['stack'][] = array(
				'controller'	=> $section['controller'],
				'method'		=> $method,
				'args'			=> $args
			);

			// Add an evaluation trace for the current part
			$trace['path']['eval'][$key] = '[  Method  ]: "'.$current.'"';
		}

		return array_merge(end($trace['stack']), array('meta' => $trace));
   	}
}
?>