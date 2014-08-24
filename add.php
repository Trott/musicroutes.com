<?php
require_once('HTMLOutput.php');
require_once('Authenticator.php');

//TODO: Put "add more by this band" type links on the discography.php pages. 

$a = new Authenticator();
if (! $a->isLoggedIn()) {
	HTMLOutput::printHeader(array('Add Info'));
	HTMLOutput::printSignIn(false);
} elseif (! empty( $_POST )) {
	$headers = 'From: rtrott@gmail.com' . "\r\n" .
       'Reply-To: rtrott@gmail.com' . "\r\n" .
       'X-Mailer: PHP/' . phpversion();
	$message = 'User: ' . $a->getName() . "\nEmail: " . $a->getEmail() . "\nIP: " . $_SERVER['REMOTE_ADDR'] . "\n\n";
	foreach ( $_POST as $key=>$value ) {
		$message .= "$key: $value\n\n";
	}
	$message = wordwrap($message, 70);
	HTMLOutput::printHeader(array('Add Info'));
	if (mail('rtrott@gmail.com', '[musicroutes]', $message, $headers)) {
		//TODO: Put in a link to go back to where you came from (if you used missed_queries).
		HTMLOutput::printConfirmation("Thanks!  We'll process the information you've sent as quickly as we can!", array('add','route'));
	} else {
		HTMLOutput::printConfirmation("A problem was encountered.  Please try again later.");
	}
} elseif (preg_grep('/^[alt]$/',array_keys($_GET)) != array() ) {
	require_once('DataInterface.php');
	$di = DataInterface::singleton();
	HTMLOutput::printHeader(array('Add Info'));
	$artists = array();
	$individuals = array();
	$albums = array();
	$artist = '';
	$album = '';
	if (array_key_exists('a',$_GET)) {
		list($artistIDs,,$count) = $di->search( $_GET['a'], FALSE, array('artist'), TRUE, 0, TRUE);
		require_once('Artist.php');
		require_once('Individual.php');
		for ($i=0; $i<$count; $i++) {
			$artists[$i] = new Artist($artistIDs[$i]);
			//TODO: HTMLOutput::printAddInfoForm should deal with getting the individuals, perhaps.
			$individuals[$i] = $artists[$i]->getRelated('individual');
		}
		if ($count==0) {
			$artist=$_GET['a'];
		}
	}
	if (array_key_exists('l', $_GET)) {
		list($albumIDs,,$count) = $di->search( $_GET['l'], FALSE, array('album'), TRUE, 0, TRUE);
		require_once('Album.php');
		for ($i=0; $i<$count; $i++) {
			$albums[$i] = new Album($albumIDs[$i]);
		}
		if ($count==0) {
			$album=$_GET['l'];
		}
	}
	HTMLOutput::printAddInfoForm('',$artist,FALSE,$artists,$album,$albums);
} else {
	require_once('DataInterface.php');
	$di = DataInterface::singleton();

	if (array_key_exists('ma',$_GET)) {
		HTMLOutput::printHeader(array('Add Info',$_GET['ma']));
		HTMLOutput::printMissedAlbums($_GET['ma'],$di->getMissedAlbums($_GET['ma']));
	} elseif (array_key_exists('ml',$_GET)) {
		HTMLOutput::printHeader(array('Add Info',$_GET['ml']));
		HTMLOutput::printMissedTracks($_GET['ml'],$di->getMissedTracks($_GET['ml']));
	} elseif ($_SERVER['QUERY_STRING']=='m') {
		HTMLOutput::printHeader(array('Add Info'));
		HTMLOutput::printMissedArtists($di->getMissedArtists());
	} else {
		HTMLOutput::printHeader(array('Add Info'));
		HTMLOutput::printMissedArtists($di->getMissedArtists(),TRUE);
		HTMLOutput::printAddInfoForm();
	}
}
HTMLOutput::printFooter();
?>