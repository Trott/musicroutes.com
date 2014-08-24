<?php

require_once('DataInterface.php');
require_once('RouteElement.php');

class RouteObject implements Iterator {

	private $di;
	private $routeElementArray;
	private $position;

	public function __construct(array $from, array $to, $myFromType='individual', $myToType='individual') {
		$this->di = DataInterface::singleton();
		$path = $this->connectNodes($from, $myFromType, $to, $myToType);
		$this->setRouteElementArray($path, $from, $myFromType, $to, $myToType);
		$this->rewind();
	}

	public function __sleep() {
		return array('routeElementArray','position');;
	}

	public function __wakeup() {
		$this->di = DataInterface::singleton();
	}

	public function current() {
		return $this->routeElementArray[$this->key()];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++$this->position;
	}
	public function rewind(){
		$this->position = 0;
	}

	public function valid(){
		return array_key_exists($this->key(),$this->routeElementArray);
	}

	public function last() {
		$this->position = $this->getCount() - 1;
	}

	private function connectNodes(array $fromId, $fromType, array $toId, $toType) {
		$from=$this->initializeAnchor($fromId,$fromType);
		$to=$this->initializeAnchor($toId,$toType);

		if (( count($from) == 0 ) || ( count($to) == 0 )) {
			return array();
		}

		$map=array();
		$thisGen=array();
		$generation = 0;

		// Check that there isn't already an overlap between individuals in $from and individuals in $to
		$returnNodes=array_intersect($from,$to);
		if ($returnNodes) {
			$returnNodes = array_map(NULL,$returnNodes,$returnNodes);
			return $this->selectAndAdjustNodePerType($returnNodes, $fromId, $fromType, $toId, $toType);
		}

		foreach ($to as $node) {
			$map[$node] = array('pred'=>-1,'cost'=>$generation);
			$thisGen[] = $node;
		}

		while (! empty($thisGen)) {
			$next=$this->di->connectVertices($thisGen,array_keys($map));
			shuffle($next);
			$thisGen=array();
			$generation++;

			foreach($next as $currentNode) {
				$thisGen[]=$currentNode['bid'];
				$map[$currentNode['bid']]= array('pred'=>$currentNode['aid'],'cost'=>$generation);
			}

			$returnNodes=array_intersect($thisGen,$from);
			if ($returnNodes) {
				$arrayOfResultsArrays = array();
				foreach ($returnNodes as $candidateNode) {
					$results = array();
					$results[0] = $candidateNode;
					$previd = $map[$results[0]]['pred'];
					while (! in_array($previd,$to)) {
						$results[] = $previd;
						$previd = $map[$previd]['pred'];
					}
					$results[]=$previd;
					$arrayOfResultsArrays[] = $results;
				}
				$selectedResult = $this->selectAndAdjustNodePerType($arrayOfResultsArrays, $fromId, $fromType, $toId, $toType);
				return $selectedResult;
			}
		}

		return array();
	}

	private function selectAndAdjustNodePerType(array $nodeCandidates, array $fromId, $fromType, array $toId, $toType) {
		$appendToStart=FALSE;
		$appendToEnd=FALSE;
		$startTracks=array();
		$endTracks=array();

		$startTracks=$this->di->getByRelatedID('track',$fromType,$fromId);

		$myNodes=array();
		foreach ($nodeCandidates as $thisNode) {
			$myNodeTracks=$this->di->getByRelatedIDIntersection('track','individual',$thisNode[0],$thisNode[1]);
			if (array_intersect($startTracks,$myNodeTracks)) {
				$myNodes[]=$thisNode;
			}
		}
		if (empty($myNodes)) {
			$appendToStart=TRUE;
		} else {
			$nodeCandidates = $myNodes;
		}

		$endTracks=$this->di->getByRelatedID('track',$toType,$toId);
		$myNodes=array();
		$size=count($nodeCandidates[0])-1;

		foreach ($nodeCandidates as $thisNode) {
			$myNodeTracks=$this->di->getByRelatedIDIntersection('track','individual',$thisNode[$size],$thisNode[$size-1]);
			if (array_intersect($endTracks,$myNodeTracks)) {
				$myNodes[]=$thisNode;
			}

			if (empty($myNodes)) {
				$appendToEnd=TRUE;
			} else {
				$nodeCandidates = $myNodes;
			}
		}

		$returnArray = $nodeCandidates[array_rand($nodeCandidates)];

		if ($appendToStart) {
			array_unshift($returnArray,$returnArray[0]);
		}
		if ($appendToEnd) {
			$returnArray[]=$returnArray[count($returnArray)-1];
		}

		if ((count($returnArray)==2) && (! array_intersect($startTracks,$endTracks))) {
			$returnArray[] = $returnArray[1];
		}

		return $returnArray;
	}

	private function initializeAnchor($input, $type) {
		switch ($type) {
			case 'artist':
			case 'album':
				$tracks = $this->di->getByRelatedID('track',$type,$input);
				return $this->di->getByRelatedID('individual','track',$tracks);
				break;
			case 'track':
				return $this->di->getByRelatedID('individual','track',$input);
				break;
			case 'individual':
				return (array) $input;
				break;
			default:
				throw new InvalidArgumentException($type);
		}
	}

	public function getCount() {
		return count($this->routeElementArray);
	}

	private function selectTrackForNode($thisId,$thatId,$pickRandom=TRUE,$relatedId=array(),$relatedIdType='',$relatedId2=array(),$relatedIdType2='') {
		$tracks=$this->di->getByRelatedIDIntersection('track','individual',$thisId,$thatId);
		if (!empty($relatedId)) {
			$relatedTracks=$this->di->getByRelatedID('track',$relatedIdType,$relatedId);
			$tracks=array_intersect($tracks,$relatedTracks);
		}
		if (!empty($relatedId2)) {
			$relatedTracks=$this->di->getByRelatedID('track',$relatedIdType2,$relatedId2);
			$tracks=array_intersect($tracks,$relatedTracks);
		}
		if ($pickRandom) {
			$selectedTrack=$tracks[array_rand($tracks)];
		} else {
			$selectedTrack=array_shift($tracks);
		}
		return $selectedTrack;
	}

	private function setRouteElementArray(array $path, array $matchingStartFrom, $startFromType, array $matchingEndTo, $endToType) {
		$returnArray = array();
		$stopIndex = count($path)-1;

		for ($i=0; $i<$stopIndex; $i++) {
			$fromIndividualId = $path[$i];
			$toIndividualId = $path[$i+1];

			$fromType='individual';
			$specialFromId=array();
			$toType='individual';
			$specialToId=array();

			if ($i==0) {
				$specialFromId=$matchingStartFrom;
				$fromType=$startFromType;
			}
			if ($i==$stopIndex-1) {
				$specialToId=$matchingEndTo;
				$toType=$endToType;
			}
			$trackId=$this->selectTrackForNode($path[$i],$path[$i+1],TRUE,$specialFromId,$fromType,$specialToId,$toType);
			$returnArray[$i] = new RouteElement($trackId,$fromIndividualId,$fromType,$toIndividualId,$toType);
			// If neighboring tracks have identical bands, see about using bandmembers to connect rather than non-bandmember backup singers
			// or horn players.
			if ($i!=0) {
				$bandIntersection=array_intersect($returnArray[$i-1]->getArtist(),$returnArray[$i]->getArtist());
				if (!empty($bandIntersection)) {
					$bandIDs=array();
					foreach ($bandIntersection as $bandObject) {
						$bandIDs[]=$bandObject->getID();
					}
					$prevIndividualPool=$this->di->getByRelatedIDDual('individual','track',$returnArray[$i-1]->getTrack()->getID(),'artist',$bandIDs);
					$currIndividualPool=$this->di->getByRelatedIDDual('individual','track',$returnArray[$i]->getTrack()->getID(),'artist',$bandIDs);
					$pool=array_intersect($prevIndividualPool,$currIndividualPool);
					if (!empty($pool) && !in_array($returnArray[$i]->getFrom(),$pool)) {
						$newGuy=$pool[array_rand($pool)];
						$returnArray[$i-1]->setTo($newGuy,'individual');
						$returnArray[$i]->setFrom($newGuy,'individual');
					}
				}
			}
		}
		$this->routeElementArray=$returnArray;
	}
}
?>