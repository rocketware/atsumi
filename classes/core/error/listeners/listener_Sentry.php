<?php

class listener_Sentry implements atsumi_Observer {

	private $dsn;
	private $release;
	private $tags;

	public function __construct($dsn, $release = null, $tags = array()) {
		$this->dsn 		= $dsn;
		$this->release 	= $release;
		$this->tags 	= $tags;
	}

	public function notify(atsumi_Observable $sender, atsumi_EventArgs $args) {
		
		$client = new Raven_Client(
			$this->dsn,
			array (
				'release' 	=> $this->release,
				'tags'		=> $this->tags
			)
		);

		$client->captureException($args->exception);
		
	}
}
?>