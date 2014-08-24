<?php
require_once( 'DiscographyDisplayStrategy.php' );
require_once( 'HTMLOutput.php' );
require_once( 'Individual.php' );

class DiscographyDisplayStrategyIndividual implements DiscographyDisplayStrategy {
	public function execute($id) {
		$individual = new Individual($id);
		$name = $individual->getToString();

		HTMLOutput::printHeader(array('',$name), array('search','route','add'));
		HTMLOutput::printDiscographyTitle($name,'individual');

		$bandArray = $individual->getRelated('artist');
		$this->printTracks($individual,$bandArray);

		$guestedWith = $individual->getGuestedWith();
		$this->printTracks($individual,$guestedWith);

		HTMLOutput::printDiscographyEnd();
		HTMLOutput::printBottomNote();
		HTMLOutput::printFooter();
		return TRUE;
	}

	protected function printTracks($individual,$bands) {
		$prevBands=array(); // If these show up in bandCombos, ignore them because we already came across them.
		foreach ($bands as $band) {
			// Get all tracks by a band that feature this individual.
			$allTracks = $band->getRelated('track',$individual);
			if (! empty($allTracks)) {
				// Separate tracks with more than one artist from the ones that are credited to just one artist
				$collabTracks=array();
				$justOneArtistTracks=array();
				foreach	($allTracks as $thisTrack) {
					if (count($thisTrack->getRelated('artist')) > 1) {
						$collabTracks[] = $thisTrack;
					} else {
						$justOneArtistTracks[] = $thisTrack;
					}
				}
				if (! empty($justOneArtistTracks)) {
					$nest = array();
					$albums = $band->getRelated('album',$justOneArtistTracks);
					foreach ($albums as $album) {
						$myTracks = $album->getRelated('track',$justOneArtistTracks);
						$nest[] = array(array($band),$album,$myTracks);
					}
					HTMLOutput::printDiscographySectionNested($nest);
				}
				if (! empty($collabTracks)) {
					$nest = array();
					$bandCombos = array();
					$albumsForCollabs = array();

					foreach ($collabTracks as $key=>$track) {
						$theseBands = $track->getRelated('artist');
						$thesePrevBands = array_intersect($theseBands, $prevBands);
						if (empty($thesePrevBands)) {
							$bandCombos[$key] = $theseBands;
							$albumsForCollabs[$key] = $track->getRelated('album');
						}
					}
					$prevBands[]=$band;

					$uniqueBandCombos = array_unique($bandCombos,SORT_REGULAR);
					foreach($uniqueBandCombos as $bandCombo) {
						$keysForThisCombo = array_keys($bandCombos,$bandCombo);
						$albumsForThisCombo=array();
						$tracksForThisCombo=array();
						foreach($keysForThisCombo as $thisKey) {
							$albumsForThisCombo = array_unique(array_merge($albumsForThisCombo,$albumsForCollabs[$thisKey]),SORT_REGULAR);
							$tracksForThisCombo[] = $collabTracks[$thisKey];
						}
						$uniqueAlbumsForThisCombo = array_unique($albumsForThisCombo,SORT_REGULAR);
						foreach($uniqueAlbumsForThisCombo as $thisAlbum) {
							$myTracks = $thisAlbum->getRelated('track',$tracksForThisCombo);
							$nest[] = array($bandCombo, $thisAlbum, $myTracks);
						}
						
					}

					HTMLOutput::printDiscographySectionNested($nest);
				}
			}
		}
	}
}
?>