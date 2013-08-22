<?php
/**
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

/**
 * Collects and renders debug data when needed
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class atsumi_Debug {
	/* CONSTANTS */

	/**
	 * Predefined debug area for general data
	 * @var string
	 */
	const AREA_GENERAL		= '__general';

	/**
	 * Predefined debug area for debug data
	 * @var string
	 */
	const AREA_DEBUG		= '__debug';

	/**
	 * Predefined debug area for database data
	 * @var string
	 */
	const AREA_DATABASE		= '__database';

	/**
	 * Predefined debug area for session data
	 * @var string
	 */
	const AREA_SESSION		= '__session';

	/**
	 * Predefined debug area for loader data
	 * @var string
	 */
	const AREA_LOADER		= '__loader';

	/**
	 * Predefined debug area for cache data
	 * @var string
	 */
	const AREA_CACHE		= '__cache';

	/* PROPERTIES */

	/**
	 * Contains records of activity to be displayed on the console
	 * @access protected
	 * @var array
	 */
	protected $consoleData = array();

	/**
	 * Contains data past to the debugger by the parser
	 * @access protected
	 * @var null|array
	 */
	protected $parserData = null;

	/**
	 * Contains data past from the controller to the view
	 * @var array
	 */
	protected $viewData = null;

	/**
	 * Contains data that was defined in the developers settings class
	 * @var null|array
	 */
	protected $configData = null;

	/**
	 * Contains references to database objects
	 * @var array
	 */
	protected $databases = array();

	/**
	 * Contains a reference to a session object
	 * @var null|object
	 */
	protected $session = null;

	/**
	 * A stack of unix timestamps used to calculate elapsed time
	 * @var array
	 */
	protected $timers = array();

	/**
	 * Weather the debugger is active and recording information
	 * @var boolean
	 */
	protected $active = false;

	/**
	 * A path to a log file used to output debug data
	 * @var string
	 */
	protected $fileLogPath 	= null;

	/**
	 * Weather the debugger should auto render itself when the script execution ends
	 * @var boolean
	 */
	protected $autoRender = true;

	/**
	 * Defined areas and the colours there display div's will be
	 * @var array
	 */
	protected $areas = array(
		self::AREA_GENERAL		=> 'lightblue',
		self::AREA_DEBUG		=> 'green',
		self::AREA_DATABASE		=> 'purple',
		self::AREA_SESSION		=> 'yellow',
		self::AREA_LOADER		=> 'orange'
	);

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new atsumi_Debug instance
	 * Note: Private prevents anything externally creating the debugger. Use static functions.
	 * @access private
	 */
	private function __construct() {}

	/**
	 * Performs any clean-up operations when the class is destroyed
	 * @access public
	 */
	public function __destruct() {
		try {
			if($this->active && $this->autoRender)
				 echo $this->render();
		} catch (Exception $e) { }
	}

	/* GET FUNCTIONS */

	/**
	 * Creates and/or returns a singlton instance of the class
	 * Note: Protected prevents anything externally calling this function. Use static functions.
	 * @access protected
	 */
	protected static function getInstance() {
		static $sInstance;

		if(!is_object($sInstance))
			$sInstance = new self;

		return $sInstance;
	}

	/**
	 * Returns weather or not the debugger is actively recording data
	 * @access public
	 * @return boolean
	 */
	public function _getActive() {
		return $this->active;
	}

	/* SET FUNCTIONS */

	/**
	 * Sets weather or not the debugger is actively recording data
	 * @access public
	 * @param boolean $val If the debugger should be recording [optional, default: true]
	 */
	public function _setActive($val = true) {
		$this->active = $val;
	}

	/**
	 * Sets weather or not the debugger should automaticlly render on destruction
	 * @access public
	 * @param boolean $val If the debugger should render on destruction
	 */
	public function _setAutoRender($val = true) {
		$this->autoRender = $val;
	}

	/**
	 * Sets a reference to the application config
	 * Note: Can only be called once to prevent view from affecting it
	 * @access public
	 * @param atsumi_AbstractAppSettings $config The settings to be added to the debugger
	 * @return boolen True on success, or False on error
	 */
	public function _setConfig($config) {
		if(!is_object($config) || !($config instanceof atsumi_AbstractAppSettings) || !is_null($this->configData))
			return false;

		$this->configData = $config->getSettings();
		return true;
	}

	/**
	 * Sets a reference to the view data
	 * Note: Can only be called once to prevent view from affecting it
	 * @access public
	 * @param mixed $data The data passed to the view
	 * @return boolen True on success, or False on error
	 */
	public function _setViewData($data) {
		if(!is_null($this->viewData))
			return false;

		$this->viewData = $data;
		return true;
	}

	/**
	 * Sets a reference to the parser data
	 * Note: Can only be called once to prevent view from affecting it
	 * @access public
	 * @param mixed $data The data returned from the parser
	 * @return boolen True on success, or False on error
	 */
	public function _setParserData($data) {
		if(!is_null($this->parserData))
			return false;

		$this->parserData = $data;
		return true;
	}

	/**
	 * Sets a reference to the session object
	 * @access public
	 * @param session_Handler $data
	 * @return boolen True on success, or False on error
	 */
	public function _setSession($data) {
		if(is_null($this->session))
			return false;

		$this->session = $data;
		return true;
	}

	/**
	 * Sets the css color of a data area
	 * @access public
	 * @param string $name The name of the area to set the color off
	 * @param string $color A valid css could string
	 * @return boolen True on success, or False on error
	 */
	public function _setArea($name, $color) {
		if(!array_key_exists($name, $this->area))
			return false;

		$this->area[$name] = $color;
		return true;
	}

	/* MAGIC METHODS */

	/**
	 * PHP Magic function, used to call methods on singleton staticly
	 * @access public
	 * @param string $name The name of the method being called
	 * @param array $arguments The arguments being passed to the method
	 * @return mixed The result of the function call
	 */
	public static function __callStatic($name, $arguments) {
		$instance = self::getInstance();

		if(method_exists($instance, '_'.$name))
			return call_user_func_array(array($instance, '_'.$name), $arguments);

		throw new Exception('Undefined call to atsumi_Debug::'.$name);
	}

	/* METHODS */

	/**
	 * Adds a reference to a database object to the debugger
	 * @access public
	 * @param mixed $database The database object reference to add to the debugger
	 */
	public function _addDatabase( /*db_InterfaceDatabase */ $database) {
		$this->databases[] = $database;
	}

	/**
	 * Adds a timer point onto a stack of timers
	 * @access public
	 */
	public function _startTimer() {
		$this->timers[] = microtime(true);
	}

	/**
	 * Ends the last timer added to the stack returning the time passed in microseconds
	 * @access public
	 * @return string
	 */
	public function _endTimer() {
		$startTime = array_pop($this->timers);
		return round((microtime(true) - $startTime), 3).' microseconds';
	}

	/**
	 * Records an event along with optional data, timer and area the data belongs to
	 * Note: This will do nothing if the debugger is not active
	 * @access public
	 * @param string $title The title of the record
	 * @param string $desc A more detailed decription of the record
	 * @param mixed $data Any data that should be associated with the record
	 * @param boolean $timer Weather or not to end the last timer on the stack and add it to the record
	 * @param string $area The area to add the data to
	 */
	public function _record($title, $desc, $data = null, $timer = false, $area = self::AREA_GENERAL) {
		if(!$this->active) return;

		$this->consoleData[] = array(
			'title'		=> $title,
			'desc'		=> $desc,
			'data'		=> $data,
			'area'		=> $area,
			'timestamp'	=>($timer ? '(Process Time: '.self::_endTimer().')' : '')
		);
	}

	/**
	 * Appends a html representation of the data to a log file
	 * @access protected
	 * @param mixed $data The data to log
	 */
	protected function appendLogFile($data) {
		$fp = fopen($this->fileLogPath, 'a');
		fwrite($fp, pretty($data).PHP_EOL);
		fclose($fp);
	}

	/**
	 * Adds a data area to the debugger
	 * @access public
	 * @param string $name The name of the area to add
	 * @param string $color A css valid color to represent the area
	 */
	public function _addArea($name, $color) {
		$this->areas[$name] = $color;
	}

	/**
	 * Formats a variable into a html5 valid string representation
	 * @access protected
	 * @param mixed $value The value to be formatted
	 * @return string A html5 valid string representation of the variable
	 */
	protected function format($value) {

		if(is_null($value))
			return '<span class="typeNull">NULL</span>';

		if(is_int($value))
			return sf('<span class="typeInt">%s</span>', $value);

		if(is_double($value))
			return sf('<span class="typeDouble">%s</span>', $value);

		if(is_bool($value))
			return sf('<span class="typeBool">%s</span>',($value ? 'true' : 'false'));

		if(is_string($value))
			return sf(
				'<span class="typeString">\'%s\'</span>',
				htmlentities(preg_replace("/\s+/", ' ', str_replace(array('\'', "\n", "\r", "\t"), array("'", ' ', '\r', ' '), $value)))
			);

		if(is_array($value)) {
			$ret = '<span class="typeArray">Array</span>(';
			foreach($value as $key => $item)
				$ret .= sf('<div class="var">[<span class="typeKey">%s</span>] => %s</div>', $key, stripos($key,'password')?'*****':$this->format($item));
			$ret .= ')';
			return $ret;
		}

		if(is_object($value)) {
			return sf('(Class) <span class="typeObject">%s</span>', get_class($value));
			/*
			if(method_exists($value,'toString'))
				return $value->toString();

			$data = $value;
			if(method_exists($value, 'dumpDebug'))
				$data = $value->dumpDebug();

			$ret = sf('<span class="typeObject">%s</span>(', get_class($value));
			foreach($data as $key => $item)
				$ret .= sf('<div class="var">[<span class="typeKey">%s</span>] => %s</div>', $key, $this->format($item));
			$ret .= ')';
			return $ret;
			*/
		}
		return sf('<span class="typeArray">(%s)%s</div>', gettype($value), $value);
	}

	/**
	 * Returns any JavaScript used by the debugger to render itself
	 * @access protected
	 * @return string The JavaScript as a string
	 */
	protected function returnJavascript() {
		return '
var DebugPlane = function () {

	this.open = false;
	this.height = 250;
	this.tab = "console";
	this.dragging = false;
	this.mousePos = true;

	this.restoreState();
	this.initUserInterface();
	this.refresh();

	document.onmousemove = this.mouseMove;
	document.onmouseup = this.mouseUp;

}

DebugPlane.prototype.restoreState = function () {
	if (this.readCookie ("debugHeight") != null)
		this.height = parseInt(this.readCookie ("debugHeight"));

	if (this.readCookie ("debugTab") != null)
		this.tab = this.readCookie ("debugTab");

	if (this.readCookie ("debugOpen") != null) {
		this.open = this.readCookie ("debugOpen");
		if (this.open == "true") this.open = true;
		else this.open = false;
	}
}
DebugPlane.prototype.saveState = function () {

	this.createCookie ("debugOpen", 	this.open, 14);
	this.createCookie ("debugHeight", 	this.height, 14);
	this.createCookie ("debugTab", 		this.tab, 14);

}

DebugPlane.prototype.createCookie = function (name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

DebugPlane.prototype.readCookie = function (name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(";");
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==" ") c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

DebugPlane.prototype.mouseMove = function (ev) {

	ev = ev || window.event;
	debugPlane.mousePos = debugPlane.mouseCoords(ev);
	document.getElementById("mouseCursorX").innerHTML = debugPlane.mousePos.x;
	document.getElementById("mouseCursorY").innerHTML = debugPlane.mousePos.y;

	if(debugPlane.dragging) {
		var clientHight = window.innerHeight;
		var newHeight = (clientHight + window.pageYOffset ) - debugPlane.mousePos.y;

		if(newHeight < 100) newHeight = 100;
		if(newHeight > (clientHight-100)) newHeight = clientHight -100;
		debugPlane.refreshHeight();
		debugPlane.height = newHeight;
	}

	return false;
}

DebugPlane.prototype.mouseCoords = function (ev) {
	if(ev.pageX || ev.pageY){
		return {x:ev.pageX, y:ev.pageY};
	}
	return {
		x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,
		y:ev.clientY + document.body.scrollTop  - document.body.clientTop
	};
}

DebugPlane.prototype.mouseUp = function (ev) {
	if (debugPlane.dragging) {
		debugPlane.dragging = false;
		debugPlane.saveState();
		document.getElementById("debugConsoleStatus").innerHTML = "Idle";

	}
}
DebugPlane.prototype.refreshHeight = function () {

		var clientHight = window.innerHeight;
		if(this.height > (clientHight-100)) this.height = clientHight -100;
		document.getElementById("debugConsole").style.height = String(this.height) + "px";
		document.getElementById("debugConsoleInner").style.height = String(this.height) + "px";
		document.getElementById("debugConsoleDisplayInner").style.height = String(this.height - 80)+"px";
		document.getElementById("debugConsoleHeight").innerHTML = this.height;

}
DebugPlane.prototype.refresh = function () {

	var plane = document.getElementById("debugConsole");
	if (this.open) {
		plane.className = "debugConsole debugOpen";
		var windowHeight = (typeof window.innerHeight != "undefined" ? window.innerHeight : document.body.offsetHeight);
		this.refreshHeight();
		this.refreshTabs();
	} else {
		plane.className = "debugConsole debugClosed";
	}
}

DebugPlane.prototype.initUserInterface = function () {

	var that = this;
	var openDebug = document.getElementById("debugOpenButton");
	openDebug.onclick = function () {
		that.open = true;
		that.refresh();
		debugPlane.saveState();
	}

	var drag = document.getElementById("debugDragBar");
	drag.onmousedown = function (ev, object) {
		that.dragging = true;
		document.getElementById("debugConsoleStatus").innerHTML = "Dragging";
		return false;
	}

	var closeDebug = document.getElementById("tabCloseDebug");
	closeDebug.onclick = function () {
		that.open = false;
		that.refresh();
		debugPlane.saveState();
	}

	var children = document.getElementById("debugTabsList").childNodes;
	var that = this;

	for (var i=0; i < children.length; i++) {
		if (children[i].nodeType == 1 && children[i].id != "tabCloseDebug")
			children[i].onclick = function (event) {
				obj = event.srcElement || event.target;
				that.selectTab(obj.getAttribute("rel"));
			}
	}
	this.refreshTabs();
	document.getElementById("debugTabContainer_"+this.tab).style.display 	= "block";
}

DebugPlane.prototype.selectTab = function (tabName) {
	if (tabName == this.tab) return;

	document.getElementById("debugTabContainer_"+this.tab).style.display 	= "none";
	document.getElementById("debugTabContainer_"+tabName).style.display 	= "block";

	this.tab = tabName;
	this.refreshTabs();
	this.saveState();
}

DebugPlane.prototype.refreshTabs = function () {

	var children = document.getElementById("debugTabsList").childNodes;
	for (var i=0; i < children.length; i++) {
		if (children[i].nodeType == 1) {
			if (children[i].getAttribute("rel") == this.tab) {
				children[i].className = "debugTabSelected";
			} else {
				children[i].className = "";
			}
		}
	}
}
var debugPlane;

window.onload=function(){
	debugPlane = new DebugPlane;
}';
	}

	/**
	 * Returns any CSS used by the debugger to render itself
	 * @access protected
	 * @return string The CSS as a string
	 */
	protected function returnCss() {
		return '

			.debugConsole { left:0px; background-color: #ededed; color: black;  width: 100% !important;  cursor: default; z-index: 999999 ; }
			.debugConsole * { font-size:13px; margin:0; padding:0; color:#000; font-family:verdana; }
			.debugConsole.debugOpen { position: fixed ; height: 250px; bottom: 0px; overflow:hidden; }
			.debugConsole.debugClosed { height:auto !important; border-top:1px outset #777; border-bottom:1px outset #777; }
			#debugOpenButton { text-shadow:0px 1px 1px #fff; color:#666;  cursor: pointer; }
			.debugConsole.debugOpen .debugOpenContainer { display:block; }
			.debugConsole.debugOpen .debugClosedContainer { display:none; }
			.debugConsole.debugClosed .debugOpenContainer { display:none; }
			.debugConsole.debugClosed .debugClosedContainer { display:block; }
			.debugConsole.debugInit .debugOpenContainer, .debugConsole.debugInit .debugClosedContainer { display:none; }
			.debugClosedContainer { display:none; padding: 0.5em 1em; text-align:right; }
			#debugConsoleInner { width:100%; }
			td.debugTabs { border-bottom:2px outset #ccc; height:25px;background-color:#dddddd;}
			td.debugTabs ul { margin:0; padding:0; }
			td.debugTabs li { display:inline; float:left; padding:5px 10px; border-right:1px solid #bbb; text-indent:0; font-size:12px; color:#555; cursor: pointer; }
			td.debugTabs li:hover { background-color:#eee; color:#333; }
			td.debugTabs li.debugTabSelected { background-color:#fff; color:#000; }
			.debugConsoleDisplay { height: 100%; vertical-align:top; display: block; text-align:left !important; background-color:#eee; padding:5px; }
			div.debugConsoleDisplayInner { overflow-y: auto; height:300px; }
			#debugDragBar { height: 10px; cursor:n-resize; border-top:1px solid #888 !important; border-bottom:1px solid #888 !important; }
			.logItem {border-width: 1px 1px 1px 10px; border-style: solid; margin: 0 0 5px 0; padding: 1em; background-color:#f8f8f8; }
			.debugConsole .var {padding-left: 16px;}
			.debugConsole .typeNull {}
			.debugConsole .typeInt {color: brown;}
			.debugConsole .typeDouble {color: RoyalBlue;}
			.debugConsole .typeBool {color: RoyalBlue;}
			.debugConsole .typeString {color: #241aa6;}
			.debugConsole .typeArray {color: RoyalBlue;}
			.debugConsole .typeKey {color: #d0832f;}
			.debugConsole .typeObject {color: LightSeaGreen;}
			.debugConsole .typeUnknown {color: orange; }
			.logTitle {font-size: 14px; font-weight: bold;}
			.logTimestamp {font-size: 10px; font-weight: normal; color: #6677DD;}
			.logDesc {font-size: 10px; font-style: italic; color:#777; margin-top:0.5em; white-space:pre-line; }
			.logData { background-color:#ddd; border:1px solid #ccc; padding:1em; margin-top:1em; white-space:pre-line; }
			.debugDragToggle {height:5px;  border-left:3px double #555; border-right:3px double #555; width:2px; margin:0 auto 0 auto; font-size:0px; display:block;  }
			.debugFooter { background-color:#eee; border-top:2px inset #ccc; height:20px; padding:5px; text-align:right; color:#777; font-size: 12px; }
			.debugFooter span { color:#777; }
			.debugConsoleClear {display:block;clear:both;}
			.debugConsoleWindow {display: none;height:100%; text-align:left !important;  }
			.debugConsoleWindowInner {padding: 2px 10px; text-align:left !important;  }

		';
	}

	/**
	 * Renders all debugger in formation in a HTML5 human readable way
	 * @access public
	 * @return string The debugger as a valid HTML5 string
	 */
	public function _render() {

		if(!$this->active) return;


		$this->startTimer();
		$display =(isset($_COOKIE['debugDisplay']) ? strtolower($_COOKIE['debugDisplay']) : 'console');
		ob_start();
?>
<style type="text/css"><?=$this->returnCss();?></style>
<script type="text/javascript"><?=$this->returnJavascript();?></script>
<div id="debugConsole" class="debugConsole debugInit">
<div class='debugClosedContainer'>
<div id='debugOpenButton'>Click here to open the Atsumi debug plane</div>
</div>
<div class='debugOpenContainer'>
	<table id="debugConsoleInner" cellpadding="0" cellspacing="0">
		<tr><td id="debugDragBar"><span class="debugDragToggle"></span><!-- Height Ajuster --></td></tr>
		<tr>
			<td id="debugTabs" class="debugTabs">
				<ul id="debugTabsList">
					<li rel="console">Console</li>
					<li rel="parser">Parser</li>
					<li rel="view">View Data</li>
					<li rel="setting">App Settings</li>
					<li rel="request">Request</li>
					<li rel="session">Session</li>
					<li rel="cookie">Cookie</li>
					<li rel="database">Databases</li>
					<li id="tabCloseDebug">Close Debug</li>
				</ul>
				<span class="debugConsoleClear"></span>
			</td>
		</tr>
		<tr>
			<td class="debugConsoleDisplay" id="debugConsoleDisplay">
				<div class="debugConsoleDisplayInner"  id="debugConsoleDisplayInner">
				<div id="debugTabContainer_console" class="debugConsoleWindow">
<? foreach($this->consoleData as $data) : ?>
					<div class="logItem" style="border-color: <?=(isset($this->areas[$data['area']]) ? $this->areas[$data['area']] : 'white');?>">
						<div class="logTitle"><?=$data['title'];?> <span class="logTimestamp"><?=$data['timestamp'];?></span></div>
						<div class="logDesc"><?=$data['desc'];?></div>
						<?=(!is_null($data['data']) ? sf('<div class="logData">%s</div>', $this->format($data['data'])) : '');?>
					</div>
<? endforeach; ?>
				</div>

				<!-- Parser Data -->
				<div id="debugTabContainer_parser" class="debugConsoleWindow">
					<div class="debugConsoleWindowInner">
						<?=$this->format($this->parserData);?>
					</div>
				</div>

				<!-- View Data -->
				<div id="debugTabContainer_view" class="debugConsoleWindow">
					<div class="debugConsoleWindowInner">
						<?=$this->format($this->viewData);?>
					</div>
				</div>

				<!-- Settings Data -->
				<div id="debugTabContainer_setting" class="debugConsoleWindow">
					<div class="debugConsoleWindowInner">
						<?=$this->format($this->configData);?>
					</div>
				</div>

				<!-- Post Data -->
				<div id="debugTabContainer_request" class="debugConsoleWindow">
					<div class="debugConsoleWindowInner">
						<div class="logItem" style="border-color: <?=(isset($this->areas[$data['area']]) ? $this->areas[$data['area']] : 'white');?>">
							<div class="logTitle">POST</div><div class="logData"><?=(isset($_POST) && count($_POST) ? $this->format($_POST) : 'NULL');?></div>
						</div>

						<div class="logItem" style="border-color: <?=(isset($this->areas[$data['area']]) ? $this->areas[$data['area']] : 'white');?>">
							<div class="logTitle">GET</div><div class="logData"><?=(isset($_GET) && count($_GET) ? $this->format($_GET) : 'NULL');?></div>
						</div>

						<div class="logItem" style="border-color: <?=(isset($this->areas[$data['area']]) ? $this->areas[$data['area']] : 'white');?>">
							<div class="logTitle">FILE</div><div class="logData"><?=(isset($_FILE) && count($_FILE) ? $this->format($_FILE) : 'NULL');?></div>
						</div>

						<div class="logItem" style="border-color: <?=(isset($this->areas[$data['area']]) ? $this->areas[$data['area']] : 'white');?>">
							<div class="logTitle">SERVER</div><div class="logData"><?=(isset($_SERVER) && count($_SERVER) ? $this->format($_SERVER) : 'NULL');?></div>
						</div>
					</div>
				</div>

				<!-- Session Data -->
				<div id="debugTabContainer_session" class="debugConsoleWindow">
					<div class="debugConsoleWindowInner">
						<?=(isset($_SESSION) && count($_SESSION) ? $this->format($_SESSION) : 'NULL');?>
					</div>
				</div>

				<!-- Cookie Data -->
				<div id="debugTabContainer_cookie" class="debugConsoleWindow">
					<div class="debugConsoleWindowInner">
						<?=(isset($_COOKIES) && count($_COOKIES) ? $this->format($_COOKIES) : 'NULL');?>
					</div>
				</div>

				<!-- Database Data -->
				<div id="debugTabContainer_database" class="debugConsoleWindow">
					<div class="debugConsoleWindowInner">
<?
foreach($this->databases as $key => $database) :
	$totalTime = 0;
	foreach($database->getQueryTimes() as $query)
		$totalTime += $query['time']
?>
						<h3>Database <?=$key;?></h3>
						<h5>Total Query time: <?=$totalTime;?></h5>
						<?=str_replace(array('\t', '\n'), array(" "," "), $this->format($database->getQueryTimes()));?>
<? endforeach; ?>
					</div>
				</div>
			</div>
			</td>
		</tr>
		<tr>
			<td class="debugFooter">
				<span id="debugConsoleStatus">Idle</span> | Height:<span id="debugConsoleHeight">260</span>px
				| X:<span id="mouseCursorX">0</span>
				| Y:<span id="mouseCursorY">0</span>
				| Debug Render Time: <?=$this->endTimer();?>
			</td>
		</tr>
	</table>
</div>
</div>
<?php
		return ob_get_clean();
	}

	/*
	 * NOTE: Below are all static functions which will be removed when php 5.3.0+ becomes the
	 * common and the PHP magic function __callStatic works correctly
	 */
	public static function getActive() {
		return self::__callStatic(__FUNCTION__, array());
	}

	public static function setActive($val) {
		$args = func_get_args();
		self::__callStatic(__FUNCTION__, $args);
	}

	public static function setAutoRender($val = true) {
		$args = func_get_args();
		self::__callStatic(__FUNCTION__, $args);
	}

	public static function setConfig($config) {
		$args = func_get_args();
		self::__callStatic(__FUNCTION__, $args);
	}

	public static function setViewData($data) {
		$args = func_get_args();
		self::__callStatic(__FUNCTION__, $args);
	}

	public static function setParserData($data) {
		$args = func_get_args();
		self::__callStatic(__FUNCTION__, $args);
	}

	public static function setSession($data) {
		$instance = self::getInstance();
		$instance->_setSession($data);
	}

	public static function setArea($name, $color) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function addDatabase($database) {
		$args = func_get_args();
		self::__callStatic(__FUNCTION__, $args);
	}

	public static function startTimer() {
		self::__callStatic(__FUNCTION__, array());
	}

	public static function endTimer() {
		return self::__callStatic(__FUNCTION__, array());
	}

	public static function record($title, $desc, $data = null, $timer = false, $area = self::AREA_GENERAL) {
		$args = func_get_args();
		self::__callStatic(__FUNCTION__, $args);
	}

	public static function addArea($name, $color) {
		$args = func_get_args();
		self::__callStatic(__FUNCTION__, $args);
	}

	public static function render() {
		return self::__callStatic(__FUNCTION__, array());
	}
}
?>