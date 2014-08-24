<?php
require_once( 'DiscographyDisplayStrategy.php' );
require_once( 'HTMLOutput.php' );
require_once( 'Artist.php' );

class DiscographyDisplayStrategyArtist implements DiscographyDisplayStrategy {
	public function execute($id) {
		$htmlo = new HTMLOutput();

		$artist = new Artist($id);
		$name = $artist->getToString();

		$htmlo->printHeader(array('',$name), array('search','route','add'));
		$htmlo->printDiscographyTitle($name,'artist');


		$htmlo->printDiscographySection('individual',$artist->getRelated('individual'),'Band Members');
		$albums = $artist->getRelated('album');
		foreach ($albums as $album) {
			$htmlo->printDiscographySection('track', $album->getRelated('track',$artist),array('album',$album));
		}

		$htmlo->printDiscographyEnd();
		$htmlo->printBottomNote();
		$htmlo->printFooter();
		return TRUE;
	}
}
?>