<?php
class HTTPRequest {

	private function stripslashes_deep($value)
	{
		if(get_magic_quotes_gpc()) {
			$value = is_array($value) ?	array_map(array($this,__FUNCTION__), $value) : stripslashes($value);
		}

		return $value;
	}
	
	public function getID() {
		return array_key_exists( 'id', $_GET ) ? $this->stripslashes_deep($_GET['id']) : '';
	}
	
	public function getValue($property, $post=FALSE) {
		if ($post) {
			$myArray =& $_POST;
		} else {
			$myArray =& $_GET;
		}
		if (! array_key_exists($property, $myArray)) {
			return FALSE;
		}
		return $this->stripslashes_deep($myArray[$property]);
	}
	
	public function formSubmitted() {
		return count($_POST) > 0;
	}
}
?>