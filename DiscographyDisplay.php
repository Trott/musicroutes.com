<?php
require_once( 'HTTPRequest.php' );

class DiscographyDisplay {
	private $id;		// database ID number
	private $strategy;	// Strategy Pattern to be implemented

	public function __construct(DiscographyDisplayStrategy $strategy) {
		$httpr = new HTTPRequest();
		$this->id = $httpr->getID();
		$this->strategy = $strategy;
	}

	public function execute() {
		if (! $this->strategy->execute($this->id)) {
			$this->goHome();
		}
	}

	protected function goHome() {
		header( 'Location: /' );
		exit;
	}
}
?>