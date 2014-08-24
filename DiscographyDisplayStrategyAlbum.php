<?php
require_once( 'DiscographyDisplayStrategy.php' );
require_once( 'HTMLOutput.php' );
require_once( 'Album.php' );

class DiscographyDisplayStrategyAlbum implements DiscographyDisplayStrategy {
	public function execute($id) {
		$htmlo = new HTMLOutput();

		$album = new Album($id);

		$name = $album->getToString();

		$htmlo->printHeader(array('',$name), array('search','route','add'));
		$htmlo->printDiscographyTitle($name,'album');

		$trackArray = $album->getRelated('track');
		$artistArray = array();
		$artistTrackArray = array();

		foreach ($trackArray as $track) {
			$thisArtist = $track->getRelated('artist');
			$artistTrackArray[$track->getID()] = $thisArtist;
			if (! in_array($thisArtist,$artistArray)) {
				$artistArray[] = $track->getRelated('artist');
			}
		}

		foreach ($artistArray as $artists) {
			$trackArray=array_keys($artistTrackArray, $artists);
			//array_walk should really do the trick here, but it may be not-ready-for-prime-time in PHP 5.2.9?
			foreach ($trackArray as $key=>$value) {
				$trackArray[$key] = new Track($value);
			}
			$htmlo->printDiscographySection('track',$trackArray, array('artist',$artists));
		}
			
		$htmlo->printDiscographyEnd();
		$htmlo->printBottomNote();
		$htmlo->printFooter();
		return TRUE;
	}
}
?>