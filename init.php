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

// Manually require the core components as loader is not initalised at this point
require_once(dirname(__FILE__).'/classes/core/loader/Loader.php');

// Load everything else
\Atsumi\Core\Loader::references(\Atsumi\Core\Loader::getAtsumiDir(), 'core');

// Start atsumi
Atsumi::start();
?>