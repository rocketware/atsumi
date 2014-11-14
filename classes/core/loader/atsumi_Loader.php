<?php

// COMPATIBILITY class...
class atsumi_Loader {

	static public function references ($args) {
		return \Atsumi\Core\Loader::references ($args);
	}
	static public function getWorkspace () {
		return \Atsumi\Core\Loader::getWorkspace ();
	}
}


?>