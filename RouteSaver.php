<?php
$sessionId = session_id();
if (empty($sessionId))
	session_start();
require_once 'DataInterface.php';
require_once 'RouteObject.php';

class RouteSaver
{
	private $di;

	public function __construct() {
		$this->di = DataInterface::singleton();
	}

	public function saveRouteSession(RouteObject $route) {
		$routeData=$route;
		$key = md5(SID.$_SERVER['REQUEST_TIME']);
		$_SESSION[$key]=$routeData;
		return $key;
	}
	
	public function retrieveRouteSession($key) {
		return $_SESSION[$key];
	}

	public function saveRoute($key) {
		$routeData=base64_encode(serialize($_SESSION[$key]));
		
		if (empty($routeData)) {
			return FALSE;
		}
		$this->di->saveRoute($key,$routeData);
		return TRUE;
	}

	public function retrieveRoute($key) {
		$routeData=$this->di->retrieveRoute($key);
		if ($routeData) {
			$route = unserialize(base64_decode($routeData));
			if (get_class($route) !== 'RouteObject') {
				throw new UnexpectedValueException('Expected RouteObject, received ' . get_class($route));
			}
			return $route;
		} else {
			return FALSE;
		}
	}
}
?>