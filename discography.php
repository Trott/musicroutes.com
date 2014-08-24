<?php
require_once( 'errorHandling.php' );
require_once( 'DiscographyDisplay.php' );
require_once( 'HTTPRequest.php' );

$httpr = new HTTPRequest();
$type = $httpr->getValue('t');

switch ($type) {
	case 'a':
	case 'artist':
		require_once( 'DiscographyDisplayStrategyArtist.php' );
		$display = new DiscographyDisplay( new DiscographyDisplayStrategyArtist() );
		break;
	case 'l':
	case 'album':
		require_once( 'DiscographyDisplayStrategyAlbum.php' );
		$display = new DiscographyDisplay( new DiscographyDisplayStrategyAlbum() );
		break;
	case 'i':
	case 'individual':
		require_once( 'DiscographyDisplayStrategyIndividual.php' );
		$display = new DiscographyDisplay( new DiscographyDisplayStrategyIndividual() );
		break;
	default:
		require_once( 'DiscographyDisplayStrategyTrack.php' );
		$display = new DiscographyDisplay( new DiscographyDisplayStrategyTrack() );
}

$display->execute();
?>