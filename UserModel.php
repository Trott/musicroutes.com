<?php
require_once('DataInterface.php');

class UserModel {
	protected $di;
	
	// Private constructor to prevent instantiation
	public function __construct() {
		$this->di = DataInterface::singleton();
	}

	public function createUser(array $properties) {
		$this->di->addEntry('user',$properties,TRUE);
	}

	public function deleteUser($identifier) {
		$id = $this->di->getByProperties('user',array('identifier'=>$identifier),FALSE,FALSE,TRUE);
		if (isset($id[0])) {
			return $this->di->deleteEntry('user',$id[0]);
		}
	}
	
	public function getUser($identifier) {
		return array_shift($this->di->getByProperties('user',array('identifier'=>$identifier)));
	}
}
?>