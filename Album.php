<?php
require_once('DiscographyElement.php');
require_once('DataInterface.php');
require_once('Artist.php');

class Album extends DiscographyElement {
	
	protected $artURL, $artURL_tn;
	
	public function __construct($id) {
		parent::__construct($id,array('artURL','artURL_tn'));
	}

	public function __sleep($values=array('artURL','artURL_tn')) {
		parent::__sleep(array('artURL','artURL_tn'));
	}

	public function getArtURL($thumbnail=FALSE) {
		return $thumbnail ? $this->artURL_tn : $this->artURL;
	}
}
?>