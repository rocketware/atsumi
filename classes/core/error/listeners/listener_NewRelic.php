<?php

class listener_NewRelic implements atsumi_Observer {

	public function notify(atsumi_Observable $sender, atsumi_EventArgs $args) {

		if (extension_loaded('newrelic')) {
			newrelic_notice_error('', $args->exception);
		}

	}
}
?>