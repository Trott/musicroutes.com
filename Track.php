<?php
require_once('DiscographyElement.php');
require_once('DataInterface.php');
require_once('Artist.php');
require_once('Album.php');
require_once('Individual.php');

class Track extends DiscographyElement {
	protected $rcid;

	public function __construct($id) {
		parent::__construct($id,array('rcid'));
	}

	public function __sleep($values=array('rcid')) {
		parent::__construct($id,array('rcid'));
	}

	public function getRCID() {
		return $this->rcid;
	}
}
?>