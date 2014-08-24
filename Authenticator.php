<?php
require_once('UserModel.php');
session_start();

class Authenticator {
	protected $um;
	
	// Private constructor to prevent instantiation
	public function __construct() {
		$this->um = new UserModel();
	}

	public function isLoggedIn() {
		return isset($_SESSION['logged_in']);
	}

	public function logIn($identifier, $email, $given_name, $family_name) {
		$userData = $this->um->getUser($identifier);
		if (empty($userData)) {
			$this->um->createUser(array('identifier'=>$identifier,
										'email'=>$email,
										'given_name'=>$given_name,
										'family_name'=>$family_name));
		}
		$_SESSION['logged_in'] = 1;
		$_SESSION['email'] = $email;
		$_SESSION['name'] = $given_name . ' ' . substr($family_name,0,1);
		$_SESSION['identifier'] = $identifier;
	}
	
	public function logOut() {
		unset($_SESSION['logged_in']);
		unset($_SESSION['email']);
		unset($_SESSION['name']);
		unset($_SESSION['identifier']);
	}
	
	public function getEmail() {
		return $_SESSION['email'];
	}
	
	public function getName() {
		return $_SESSION['name'];
	}
	
	public function getIdentifier() {
		return $_SESSION['identifier'];
	}
}
?>