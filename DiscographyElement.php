<?php
abstract class DiscographyElement
{
	protected $di;
	protected $id, $tostring, $sortorder;
	
	public function __construct($id,$values=array()) {
		$this->di = DataInterface::singleton();
		$this->id = intval($id);
		$myValues = array_merge(array('tostring','sortorder'),$values);
		$info = $this->di->getByID(strtolower($this->getType()),$this->id,$myValues);
		if (! empty($info[0])) {
			foreach ($myValues as $value) {
				$this->$value = $info[0][$value];
			}
		} else {
			foreach ($myValues as $value) {
				switch ($value) {
					case 'tostring':
					case 'sortorder':
						$this->$value = '?????';
						break;
					default:
						$this->$value = '';
				}
			}
		}
	}

	public function __sleep($save=array()) {
		return array_merge(array('id','tostring','sortorder'),$save);
	}
	
	public function __toString() {
		return $this->tostring;
	}

	public function getType() {
		return strtolower(get_class($this));
	}

	public function getID() {
		return $this->id;
	}

	public function getToString() {
		return $this->__toString();
	}

	public function getSortOrder() {
		return $this->sortorder;
	}

	public function getRelated($type,$other=null,$exclude=array()) {
		//NOTE: You can have $other for other criteria, or you can have $exclude for elements to exclude, but you cannot have both.
		if ((! empty($other)) && (! empty($exclude))){
			throw new InvalidArgumentException('$other and $exclude may not both be specified');
		}
		// $callback is a function that calls getID method on an object passed to it
		$callback = create_function('$entity', 'return call_user_func(array($entity, "getID"));');
		$returnArray = array();
		if ($other===null) {
			if (! is_array($exclude)) {
				$exclude = array($exclude);
			}
			$excludeIDs = array_map($callback,$exclude);
			$relatedIDs = $this->di->getByRelatedID($type,$this->getType(),$this->getID(),$excludeIDs);
		} else {
			if (! is_array($other)) {
				$other = array($other);
			}
			$otherIDs = array_map($callback,$other);
			//NOTE: This code assumes that $other only contains one type of object.  Only Artists or Tracks, but not both, for example.
			$relatedIDs = $this->di->getByRelatedIDDual($type,$this->getType(),$this->getID(),$other[0]->getType(),$otherIDs);
		}
		foreach($relatedIDs as $relatedID) {
			$returnArray[] = $this->create($type,$relatedID);
		}
		return $returnArray;
	}
	
    private function create($type,$id,$values=array()) {
    	$type = ucfirst($type);
        if (include_once  $type . '.php') {
            return new $type($id,$values);
        } else {
            throw new Exception ('Type not found');
        }
    }

	public function getContextSpecificData(DiscographyElement $context) {
		return $this->di->getCSD($this->getType(),$this->getID(),$context->getType(),$context->getID());
	}
}
?>