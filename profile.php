<?php
require_once('HTMLOutput.php');
require_once('Authenticator.php');
$a = new Authenticator();

HTMLOutput::printHeader(array('Profile'));
if (! $a->isLoggedIn()) {
	HTMLOutput::printSignIn(false);
} else {
	HTMLOutput::printProfile($a);
}
HTMLOutput::printFooter();
?>