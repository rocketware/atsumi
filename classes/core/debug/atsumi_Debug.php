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

	// GET FUNCTIONS

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
		return $active;
	}

	// SET FUNCTIONS

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
	public function _addDatabase(db_InterfaceDatabase $database) {
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
				$ret .= sf('<div class="var">[<span class="typeKey">%s</span>] => %s</div>', $key, $this->format($item));
			$ret .= ')';
			return $ret;
		}

		if(is_object($value)) {
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
			function Show(id) {
				HideAll();
				setRememberCookie(id);

				var element, button;
				if((element = document.getElementById(\'DebugConsoleWindow\'+id)))
					element.style.display = \'block\';
				if((button = document.getElementById(\'Button\'+id)))
					button.className += \' Selected\';

				return false;
			}

			function ShowAll() {
				var elements = document.getElementsByClassName(\'DebugConsoleWindow\');

				for(var i in elements) {
					if(elements[i].style)
						elements[i].style.display = \'block\';
				}
			}

			function HideAll() {
				var elements = document.getElementsByClassName(\'DebugConsoleWindow\');
				var buttons = document.getElementsByClassName(\'DebugConsoleMenuButton\');

				for(var i in buttons) {
					if(buttons[i])
						buttons[i].className = \'DebugConsoleMenuButton\';
				}

				for(var i in elements) {
					if(elements[i].style)
						elements[i].style.display = \'none\';
				}
			}

			function setRememberCookie(value) {
				var cookie = \'debugDisplay=\'+value;
				document.cookie = cookie;
			}

			// Resize Stuff
			document.onmousemove = mouseMove;
			document.onmouseup = mouseUp;
			var dragging = false;
			var currentY = 0;
			var mousePos = 0;
			var newY = 0;

			function mouseMove(ev) {
				ev = ev || window.event;
				mousePos = mouseCoords(ev);
				document.getElementById(\'MouseCursorX\').innerHTML = mousePos.x;
				document.getElementById(\'MouseCursorY\').innerHTML = mousePos.y;
				document.getElementById(\'DebugConsoleHeight\').innerHTML = currentY;
				document.getElementById(\'DebugConsoleNewHeight\').innerHTML = currentY;

				var debugDisplay = document.getElementById(\'DebugConsole\');

				var clientHight = document.documentElement.clientHeight;
				if(dragging) {
					var newHeight =(newY +(currentY - mousePos.y));
					if(newHeight < 7) newHeight = 7;
					if(newHeight > clientHight) newHeight = clientHight;
					document.getElementById(\'DebugConsoleNewHeight\').value = newHeight;
					debugDisplay.style.height = newHeight + \'px\';
				}

				if(debugDisplay.offsetHeight > clientHight) {
					debugDisplay.style.height = clientHight + \'px\';
				}
			}

			function mouseUp(ev){
				dragging = false;
				currentY = 0;
			}

			function mouseCoords(ev){
			if(ev.pageX || ev.pageY){
				return {x:ev.pageX, y:ev.pageY};
			}
			return {
				x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,
				y:ev.clientY + document.body.scrollTop  - document.body.clientTop
				};
			}

			function startDrag(ev, object) {
				dragging = true;
				currentY = mousePos.y;
				newY = document.getElementById(\'DebugConsole\').offsetHeight;
				return false;
			}
		';
	}

	/**
	 * Returns any CSS used by the debugger to render itself
	 * @access protected
	 * @return string The CSS as a string
	 */
	protected function returnCss() {
		return '
			#DebugConsole {background-color: #ededed; color: #black; font-size:15px !important; height: 260px; width: 100%;  cursor: default; overflow: hidden; }
			#DebugConsole, #DebugConsole table, #DebugConsole ul {font-family: \'trebuchet ms\', trebuchet, verdana, sans-serif;}
			#DebugConsoleInner {height:100%;width:100%;}
			#DebugConsoleHightAdjuster {height: 7px; cursor:n-resize; background-color: #ffffff;}
			#DebugConsoleHightAdjuster td {border-bottom: 1px solid #a3a3a3;}
			.DebugConsoleToggle {height:4px;border-width:0px 1px;border-color:#5d453d;border-style:solid;margin:1px auto;display:block;padding:0 1px;}
			#DebugConsole h3 {font-size: 22px !important; font-weight: bold; text-decoration: underline; margin: 0px; padding: 0px;}
			#DebugConsoleMenu {border-bottom:1px solid #a3a3a3; height:32px;background-color:#dddddd;}
			#DebugConsoleMenu ul {padding:0;margin:0;}
			#DebugConsoleMenu .DebugConsoleMenuButton {margin: 6px 6px 0 6px; display: inline; padding: 4px; cursor: pointer; font-size:1em !important; }
			#DebugConsoleMenu .DebugConsoleMenuButton:HOVER {margin: 5px 5px 0 5px; border-width: 1px 1px 0 1px; border-style: solid; border-color: white; background-color: CadetBlue;}
			#DebugConsoleMenu .DebugConsoleMenuButton.Selected {margin: 5px 5px 0 5px; border-width: 1px 1px 0 1px; border-style: solid; border-color: black; background-color: CadetBlue;}
			#DebugConsoleDisplay {height: 100%; vertical-align:top; overflow-y: auto; display: block; text-align:left !important; }
			.DebugConsoleWindow {display: none;height:100%; text-align:left !important;  }
			.DebugConsoleWindowInner {padding: 2px 10px; text-align:left !important;  }
			#DebugConsoleRenderTime {border-top:1px solid #cacaca;height:22px;background-color: #f9f9f9; padding:0 6px; text-align: right;}
			.logItem {border-width: 1px 1px 1px 10px; border-style: solid; margin: 2px 0px; padding: 2px;}
			.logTitle {font-size: 16px; font-weight: bold;}
			.logTimestamp {font-size: 10px; font-weight: normal; color: #6677DD;}
			.logDesc {font-size: 10px; font-style: italic;}
			.logData {background-color: darkgrey;}
			.var {padding-left: 16px;}
			.typeNull {}
			.typeInt {color: brown;}
			.typeDouble {color: RoyalBlue;}
			.typeBool {color: RoyalBlue;}
			.typeString {color: #241aa6;}
			.typeArray {color: RoyalBlue;}
			.typeKey {color: #d0832f;}
			.typeObject {color: LightSeaGreen;}
			.typeUnknown {color: orange;
			.DebugConsoleClear {display:block;clear:both;}
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
<div id="DebugConsole">
	<table id="DebugConsoleInner" cellpadding="0" cellspacing="0">
		<tr id="DebugConsoleHightAdjuster" onmousedown="return startDrag(this);"><td><span class="DebugConsoleToggle" style="width: 7px"><span class="DebugConsoleToggle" style="width: 1px;"></span></span><!-- Height Ajuster --></td></tr>
		<tr>
			<td id="DebugConsoleMenu">
				<ul>
					<li id="ButtonConsole" class="DebugConsoleMenuButton<?=($display == 'console' ? ' Selected' : '');?>" onClick="return Show('Console');">Console</li>
					<li id="ButtonParser" class="DebugConsoleMenuButton<?=($display == 'parser' ? ' Selected' : '');?>" onClick="return Show('Parser');">Parser</li>
					<li id="ButtonView" class="DebugConsoleMenuButton<?=($display == 'view' ? ' Selected' : '');?>" onClick="return Show('View');">View</li>
					<li id="ButtonSettings" class="DebugConsoleMenuButton<?=($display == 'settings' ? ' Selected' : '');?>" onClick="return Show('Settings');">Settings</li>
					<li id="ButtonPost" class="DebugConsoleMenuButton<?=($display == 'post' ? ' Selected' : '');?>" onClick="return Show('Post');">Post</li>
					<li id="ButtonGet" class="DebugConsoleMenuButton<?=($display == 'get' ? ' Selected' : '');?>" onClick="return Show('Get');">Get</li>
					<li id="ButtonFile" class="DebugConsoleMenuButton<?=($display == 'file' ? ' Selected' : '');?>" onClick="return Show('File');">File</li>
					<li id="ButtonSession" class="DebugConsoleMenuButton<?=($display == 'session' ? ' Selected' : '');?>" onClick="return Show('Session');">Session</li>
					<li id="ButtonCookie" class="DebugConsoleMenuButton<?=($display == 'cookie' ? ' Selected' : '');?>" onClick="return Show('Cookie');">Cookie</li>
					<li id="ButtonServer" class="DebugConsoleMenuButton<?=($display == 'server' ? ' Selected' : '');?>" onClick="return Show('Server');">Server</li>
					<li id="ButtonDatabases" class="DebugConsoleMenuButton<?=($display == 'databases' ? ' Selected' : '');?>" onClick="return Show('Databases');">Databases</li>
					<li id="ButtonAll" class="DebugConsoleMenuButton" onClick="return ShowAll();">Show All</li>
				</ul>
				<span class="DebugConsoleClear"></span>
			</td>
		</tr>
		<tr>
			<td id="DebugConsoleDisplay">
				<div id="DebugConsoleWindowConsole" class="DebugConsoleWindow"<?=($display == 'console' ? ' style="display: block;"' : '');?>>
<? foreach($this->consoleData as $data) : ?>
					<div class="logItem" style="border-color: <?=(isset($this->areas[$data['area']]) ? $this->areas[$data['area']] : 'white');?>">
						<div class="logTitle"><?=$data['title'];?> <span class="logTimestamp"><?=$data['timestamp'];?></span></div>
						<div class="logDesc"><?=$data['desc'];?></div>
						<?=(!is_null($data['data']) ? sf('<div class="logData">%s</div>', $this->format($data['data'])) : '');?>
					</div>
<? endforeach; ?>
				</div>

				<!-- Parser Data -->
				<div id="DebugConsoleWindowParser" class="DebugConsoleWindow"<?=($display == 'parser' ? ' style="display: block;"' : '');?>>
					<div class="DebugConsoleWindowInner">
						<?=$this->format($this->parserData);?>
					</div>
				</div>

				<!-- View Data -->
				<div id="DebugConsoleWindowView" class="DebugConsoleWindow"<?=($display == 'view' ? ' style="display: block;"' : '');?>>
					<div class="DebugConsoleWindowInner">
						<?=$this->format($this->viewData);?>
					</div>
				</div>

				<!-- Settings Data -->
				<div id="DebugConsoleWindowSettings" class="DebugConsoleWindow"<?=($display == 'settings' ? ' style="display: block;"' : '');?>>
					<div class="DebugConsoleWindowInner">
						<?=$this->format($this->configData);?>
					</div>
				</div>

				<!-- Post Data -->
				<div id="DebugConsoleWindowPost" class="DebugConsoleWindow"<?=($display == 'post' ? ' style="display: block;"' : '');?>>
					<div class="DebugConsoleWindowInner">
						<?=(isset($_POST) && count($_POST) ? $this->format($_POST) : 'NULL');?>
					</div>
				</div>

				<!-- Get Data -->
				<div id="DebugConsoleWindowGet" class="DebugConsoleWindow"<?=($display == 'get' ? ' style="display: block;"' : '');?>>
					<div class="DebugConsoleWindowInner">
						<?=(isset($_GET) && count($_GET) ? $this->format($_GET) : 'NULL');?>
					</div>
				</div>

				<!-- File Data -->
				<div id="DebugConsoleWindowFile" class="DebugConsoleWindow"<?=($display == 'file' ? ' style="display: block;"' : '');?>>
					<div class="DebugConsoleWindowInner">
						<?=(isset($_FILE) && count($_FILE) ? $this->format($_FILE) : 'NULL');?>
					</div>
				</div>

				<!-- Session Data -->
				<div id="DebugConsoleWindowSession" class="DebugConsoleWindow"<?=($display == 'session' ? ' style="display: block;"' : '');?>>
					<div class="DebugConsoleWindowInner">
						<?=(isset($_SESSION) && count($_SESSION) ? $this->format($_SESSION) : 'NULL');?>
					</div>
				</div>

				<!-- Cookie Data -->
				<div id="DebugConsoleWindowCookie" class="DebugConsoleWindow"<?=($display == 'cookie' ? ' style="display: block;"' : '');?>>
					<div class="DebugConsoleWindowInner">
						<?=(isset($_COOKIES) && count($_COOKIES) ? $this->format($_COOKIES) : 'NULL');?>
					</div>
				</div>

				<!-- Server Data -->
				<div id="DebugConsoleWindowServer" class="DebugConsoleWindow"<?=($display == 'server' ? ' style="display: block;"' : '');?>>
					<div class="DebugConsoleWindowInner">
						<?=(isset($_SERVER) ? $this->format($_SERVER) : 'NULL');?>
					</div>
				</div>

				<!-- Database Data -->
				<div id="DebugConsoleWindowDatabases" class="DebugConsoleWindow"<?=($display == 'databases' ? ' style="display: block;"' : '');?>>
					<div class="DebugConsoleWindowInner">
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
			</td>
		</tr>
		<tr>
			<td id="DebugConsoleRenderTime">
				| Height:<span id="DebugConsoleHeight">260</span>px
				| NewHeight:<span id="DebugConsoleNewHeight">260</span>px
				| X:<span id="MouseCursorX">0</span>
				| Y:<span id="MouseCursorY">0</span>
				| Debug Render Time: <?=$this->endTimer();?>
			</td>
		</tr>
	</table>
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
		self::__callStatic(__FUNCTION__, array());
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