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
 * Advanced URI Parser for complex uri parsing
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class uriparser_Gyokuro implements uriparser_Interface {
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
	protected function createUriArr($uri) {
		$uri		= str_replace('__', '/_', $uri);
		$uriArr 	= explode('/', $uri);
		return self::trimUriArr($uriArr);
	}

	/**
	 * Removes blank elements at the beginning and end of the array
	 * @access protected
	 * @param string $uri The uri to be converted
	 * @return array The URI as an array
	 */
	protected static function trimUriArr($uriArr) {
		if(trim($uriArr[0]) == '') array_shift($uriArr);
		if(trim(end($uriArr)) == '') array_pop($uriArr);
		return $uriArr;
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

			$out = self::searchSpec($class, $controller, $newPathArr);
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
   	 * @static
   	 * @access public
   	 * @param array $specification The specification to use to create
   	 * @param string $controller The name of the controller to be used
   	 * @param string $method The name of the method to be used
   	 * @param array $args A one dimentional array of args to pass to the method
   	 */
	public function createUri($specification, $controller, $method, $args = array()) {
		$components = self::searchSpec($specification, $controller);

		// TODO: add args parser!!
		$path = count($components) ? implode('/',$components).'/'. $method.'/':'';
		return $path;
	}


	/**
	 * Parsers a uri using the given specification to determine the required controller, method and args
	 * @access public
	 * @param string $uri The uri to parse
	 * @param array $specfication The specification to parse against
	 */
	public function parseUri($uri, $specification) {

		/* strip get parameters if any */
		if(strpos($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));

		$pathSlice = $path = $this->createUriArr($uri);
		//if(empty($pathSlice)) $pathSlice = array("");
		$specSlice = $specification;
		$stackKey = 0;

		$meta = array("spec"=>array(), "stack"=>array());

		// test: does this work....
		if(array_key_exists("", $specSlice)) {
			$specKey = 0;
			$stackKey = 0;
			// add the base
			$meta['spec'][] = array(
				"controller"	=> $specSlice['']
			);
			$meta['stack'][] = array(
				"controller" 	=> $specSlice[''],
				"method"		=> null,
				"args"			=> array()
			);

			// If there's no page_index method in controller then send to methodlessRequest
			if(method_exists($meta['spec'][$specKey]['controller'], 'page_index')) {
				$meta['spec'][$specKey]['method'] 		= array(array("name" => "page_index", "args"=>array("/")));
				$meta['stack'][$stackKey]['method'] 	= "page_index";
			}
			elseif(!count($pathSlice)) {
				$path =((count($pathSlice) > 0) ? sf("/%s/", implode("/", $pathSlice)) : "/");
				$meta['spec'][$specKey]['method']	= array(array("name" => "methodlessRequest", "args"=>array($path)));
				$meta['stack'][$stackKey]			= array("controller"=>$meta['spec'][$specKey]['controller'], "method"=>"methodlessRequest", "args"=>array($path));
			}
		}

		while(count($pathSlice)) {

			// loop through the path components
			$specKey = count($meta['spec'])? count($meta['spec'])-1:0;
			$stackKey = count($meta['stack'])?count($meta['stack'])-1:0;

			if(array_key_exists($pathSlice[0], $specSlice)) {

				// The path slice is referenced in the site spec
				if(is_array($specSlice[$pathSlice[0]])) {

					// The path slice is an array in the spec
					// TODO: there may not be a [''] entry....
					$meta['spec'][] 	= array("controller" => array_key_exists("", $specSlice[$pathSlice[0]]) ? $specSlice[$pathSlice[0]][''] : $specSlice[$pathSlice[0]]);
					$meta['stack'][] 	= array(
						"controller" 	=> array_key_exists("", $specSlice[$pathSlice[0]]) ? $specSlice[$pathSlice[0]][''] : $specSlice[$pathSlice[0]],
						"method"		=> null,
						"args"			=> array()
					);

					// think this needs to chop at the point in the array...
					$specSlice = is_array($specSlice[$pathSlice[0]]) ? $specSlice[$pathSlice[0]] : array($pathSlice[0] => $specSlice);

				}
				else {
					// The path slice references a controller
					// Set controller & set defaults...
					$meta['spec'][] 	= array("controller" => $specSlice[$pathSlice[0]]);
					$meta['stack'][] 	= array(
						"controller"	=> $specSlice[$pathSlice[0]],
						"method"		=> null,
						"args"			=> array()
					);

					$specSlice = is_array($specSlice[$pathSlice[0]]) ? $specSlice[$pathSlice[0]] : array($pathSlice[0] => $specSlice);
				}
				$stackKey++;
				$specKey++;

				// If there's no page_index method in controller then send to methodlessRequest
				if(method_exists($meta['spec'][$specKey]['controller'], "page_index")) {
					$meta['spec'][$specKey]['method'] 		= array(array("name" => "page_index", "args" => array()));
					$meta['stack'][$stackKey]['method'] 	= "page_index";
				}
				else {
					$meta['spec'][$specKey]['method']	= array(array("name" => "methodlessRequest", "args" => array("/")));
					$meta['stack'][$stackKey]			= array("controller" => $meta['spec'][$specKey]['controller'], "method" => "methodlessRequest", "args" => array("/"));
				}

				$pathSlice = array_slice($pathSlice,1);
				// The path slice references a method in the controller
			}
			elseif(method_exists($meta['spec'][$specKey]['controller'], "page_".$pathSlice[0])) {
				/* TODO: finish this when PHP 5.3 is available...
					if(count($meta['spec'][$specKey]['method']) &&
						!self::controllerGrantsPermissionToNestMethods(
							$meta['spec'][$specKey]['controller'],
							$meta['spec'][$specKey]['method'],
							"page_".$pathSlice[0]
						)
					)
					throw new Exception("Permission denied to nest methods in controller");
				*/

				// Does it match a method?
				$meta['spec'][$specKey]['method'][] = array("name"=> "page_".$pathSlice[0], "args"=>array());
				$meta['stack'][] = array(
					"controller" 	=> $meta['stack'][$stackKey]['controller'],
					"method" 		=> "page_".$pathSlice[0],
					"args"			=> array()
				);

				$pathSlice = array_slice($pathSlice,1);
			}
			elseif(substr($pathSlice[0],0,1) == "_") {
				// Doesn't match controller or method...
				if(!array_key_exists('method', $meta['spec'][$specKey]))
					$meta['spec'][$specKey]['method'][] = array("name" => "page_index", "args" => array());

				$meta['spec'][$specKey]['method'][count($meta['spec'][$specKey]['method'])-1]['args'][] = substr($pathSlice[0],1);
				$meta['stack'][$stackKey]['args'][] = substr($pathSlice[0],1);
				$pathSlice = array_slice($pathSlice,1);
			}
			else {
				// methodless request
				$path =((count($pathSlice) > 0) ? sf("/%s/", implode("/", $pathSlice)) : "/");
				$meta['spec'][$specKey]['method'][] = array("name"=> "methodlessRequest", "args" => array($path));
				$meta['stack'][] = array(
					"controller"	=> $meta['stack'][$stackKey]['controller'],
					"method"		=> "methodlessRequest",
					"args"			=> array($path)
				);
				$pathSlice = array_slice($pathSlice,1);
			}
		}

		$meta['path'] = array(
			"raw"			=> $uri,
			"arr"			=> $this->createUriArr($uri),
			"components"	=> $path
		);

		return array_merge(
			$meta['stack'][(count($meta['stack'])-1)],
			array("meta" => $meta)
		);
   	}
}
?>