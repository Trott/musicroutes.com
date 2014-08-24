<?php
require_once( 'errorHandling.php' );
require_once( 'DiscographyDisplayStrategy.php' );
require_once( 'Track.php' );
require_once( 'HTMLOutput.php' );

class DiscographyDisplayStrategyTrack implements DiscographyDisplayStrategy {
	public function execute($id) {
		$htmlo = new HTMLOutput();
		
		$track = new Track($id);
		$name = $track->getToString();

		$htmlo->printHeader(array('',$name), array('search','route','add'));
		$htmlo->printDiscographyTitle($name,'track');

		$htmlo->printDiscographySection('artist',$track->getRelated('artist'),'Recorded by',null,TRUE);
		$htmlo->printDiscographySection('album',$track->getRelated('album'),'On the album(s)');
		$htmlo->printDiscographySection('individual',$track->getRelated('individual'),'Credits',$track);	

		$htmlo->printDiscographyEnd();
		$htmlo->printBottomNote();
		$htmlo->printFooter();
		return TRUE;
	}
}
?>