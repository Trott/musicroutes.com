<?php
require_once('Track.php');
require_once('Album.php');
require_once('Artist.php');
require_once('Individual.php');

class RouteElement {
	private $trackID;
	private $track;
	private $album;
	private $artist;
	private $from, $fromType;
	private $to, $toType;
	private $fromId, $toId;

	//NOTE: $fromID/$toID are individual IDs.
	public function __construct($trackID, $fromID, $fromType, $toID, $toType) {
		$this->trackID = $trackID;
		$this->track = new Track($this->trackID);
		$this->album = $this->track->getRelated('album');
		$this->artist = $this->track->getRelated('artist');
		$this->setFrom($fromID,$fromType);
		$this->setTo($toID,$toType);
	}

	public function __sleep() {
		return array('trackID','fromID','fromType','toID','toType');
	}

	public function __wakeup() {
		$this->track = new Track($this->trackID);
		$this->album = $this->track->getRelated('album');
		$this->artist = $this->track->getRelated('artist');
		$this->from = $this->createDiscographyElement($this->fromID,$this->fromType);
		$this->to = $this->createDiscographyElement($this->toID,$this->toType);
	}

	private function createDiscographyElement( $id, $type ) {
		switch($type) {
			case 'track':
				return $this->getTrack();
			case 'album':
				return $this->getAlbum();
			case 'artist':
				return $this->getArtist();
			case 'individual':
				return new Individual($id);
			default:
				throw new InvalidArgumentException('Variable $type has unexpected value: '.$type);
		}

	}

	public function getTrack() {
		return $this->track;
	}

	public function getAlbum($all=FALSE) {
		return $all ? $this->album : $this->album[0];
	}

	public function getArtist($all=TRUE) {
		return $all ? $this->artist : $this->artist[0];
	}

	public function getFrom() {
		if (is_array($this->from)){
			return $this->from;
		}
		return array($this->from);
	}

	public function setFrom($id,$type) {
		$this->fromID = $id;
		$this->fromType = $type;
		$this->from = $this->createDiscographyElement($id,$type);
	}

	public function getFromType() {
		return $this->fromType;
	}

	public function getTo() {
		if (is_array($this->to)) {
			return $this->to;
		}
		return array($this->to);
	}
	
	public function setTo($id,$type) {
		$this->toID = $id;
		$this->toType = $type;
		$this->to = $this->createDiscographyElement($id,$type);
	}

	public function getToType() {
		return $this->toType;
	}
}
?>