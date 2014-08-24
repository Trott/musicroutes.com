<?php
error_reporting(0);
$reportee='rtrott@gmail.com';
$reporting_method = 1;
if ( isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME']=='localhost' ) {
	error_reporting(E_ALL | E_STRICT);
	$reportee='/var/tmp/trott';
	$reporting_method = 3; 
}

function exception_handler($exception) {
	if ( isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME']=='localhost' ) {
		echo "<pre>Uncaught exception: " , $exception, "\n</pre>";
	}
	reportError( $exception, TRUE);
}

set_exception_handler('exception_handler');


function reportError( $message='', $email=false, $html=true ) {
	global $reportee, $reporting_method;
	if ($html) {
		echo "<b>An error occurred.  Please wait a few minutes and try again.</b><br/>\n";
	}

	if ( $message || $email ) {
		error_log( "$message\n" . mysql_error() . print_r( isset($_GET) ? $_GET : 'No GET', true ) .
			print_r( isset($_POST) ? $_POST : 'No POST' , true ) . print_r( isset($_SERVER) ? $_SERVER : 'No SERVER', true ) . 
			print_r( isset($_SESSION) ? $_SESSION : 'No SESSION', true), $reporting_method, $reportee );
	}
}
?>
