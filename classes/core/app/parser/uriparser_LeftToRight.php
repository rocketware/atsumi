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
 * Basic URI Parser for simple uri parsing
 * TODO: Clean this up or remove it??
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class uriparser_LeftToRight implements uriparser_Interface {

	public function createUri($specification, $controller, $method, $args = array()) {
	}

	public function parseUri($path, $specfication) {

			$controllerData 	= self::getControllerName($path, $specfication);


			$methodData 		= self::getMethodAndArgs($path, $controllerData['methodDepth'], $controllerData['controllerName']);

			return array(	"controller"	=> $controllerData['controllerName'],
							"method"		=> $methodData['methodName'],
							"args" 			=> $methodData['methodArgs'],
							"meta"			=> array('path'=>array('arr'=>array_slice(explode("/",$path),1))));

	}

	static public function getMethodAndArgs($path, $methodDepth, $controllerName) {
		$method = null;
			$args = array();


			$pathArr 		= explode("/", $path);
			$pathAtDepth 	= array_key_exists($methodDepth,$pathArr) ? str_replace(".","_",$pathArr[$methodDepth]) : "";

			// identify the method
			if(!array_key_exists($methodDepth, $pathArr) || empty($pathAtDepth)) $method = "page_index";
			elseif(preg_match('/^([a-zA-Z0-9\_]+)__([a-zA-Z0-9\_\-\+\%\.]+)/', $pathAtDepth, $matches) && method_exists($controllerName, sf("page_%s",$matches[1]))) {
			// is it a dynamic uri such as: domain.com/products/edit__313

				$method = sf("page_%s",$matches[1]);
				$args[] = $matches[2];
			} elseif(method_exists($controllerName, sf("page_%s",$pathAtDepth))) {
				$method = sf("page_%s",$pathAtDepth);
			} else {
			// give the controller chance to handle the request or throw a 404
				$method = 'methodlessRequest';
				$args[] = implode("/", array_slice($pathArr, $methodDepth));
			}

			return array(	"methodName" 	=> $method,
							"methodArgs"	=> $args
						);
	}

	static public function getControllerName($path, $specfication) {

			$pathArr 		= explode("/", $path);


			// Handle nested arrays for big site structures
			$i=0;
			$specSlice = $specfication;

			/*
			 *  Known bug in the following statement, if type is array and is accessed without trailing /
			 */
			while(array_key_exists($pathArr[++$i], $specSlice) && is_array($specSlice[$pathArr[$i]])) {
				$specSlice = $specSlice[$pathArr[$i]];
			}

	// 		TODO: commented this out as casues issues with mehtods off root
	//		if(!array_key_exists($pathArr[$i], $specSlice))
	//			throw new Exception("TODO: Error 404 needs to go here");

			$controllerName = str_replace(".","_",$pathArr[$i]);
			$controllerDepth 	= $i;
			// FIND THE METHOD DEPTH...
			// TODO: This should find the last instance of a valid method...


			// set the controller path
			// TODO: This is buggy - if controller is / and method is: /login/ then controllerPath is /login instead of /

			$controllerPath = implode("/", array_slice($pathArr,0,$i+1));


			if(isset($specSlice[$controllerName]) && !isset($specSlice[$controllerName])) throw new Exception("TODO: 404");

			// handle unknown path
			if(!isset($specSlice[$controllerName]) && isset($specSlice[""])) {
				$controller 	= $specSlice[""];
				$methodDepth 	= $i;
			} else {

			// handle unknown mentioned class
			if(!class_exists($specSlice[$controllerName]))
				throw new Exception(sf("Class does not exist : %s", $specSlice[$controllerName]));

				$controller 	= $specSlice[$controllerName];
				$methodDepth 	= $i+1;

			}

			return array(	"controllerName" 	=> $controller,
							"controllerDepth"	=> $controllerDepth,
							"methodDepth"		=> $methodDepth
						);

	}



	}
?>