<?php
require_once('DiscographyElement.php');
require_once('Artist.php');

class Individual extends DiscographyElement {
	public function getGuestedWith() {
		// Returns array of bands that the individual is not a member of but has recorded with
		$returnArray = array ();
		$bands = $this->di->getByRelatedID('artist','individual',$this->getID());
		$bandTracks = $this->di->getByRelatedID('track','artist',$bands);
		$tracks = $this->di->getByRelatedID('track','individual',$this->getID(),$bandTracks);
		$guestedWith = $this->di->getByRelatedID('artist','track',$tracks,$bandTracks);
		foreach ($guestedWith as $artist) {
			$returnArray[] = new Artist($artist);
		}
		return $returnArray;
	}
}
?>