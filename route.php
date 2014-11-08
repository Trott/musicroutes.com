<?php
require_once( 'errorHandling.php' );
require_once( 'HTMLOutput.php' );
require_once( 'HTTPRequest.php' );
require_once( 'DataInterface.php' );
require_once( 'RouteObject.php' );
require_once( 'RouteSaver.php' );
require_once( 'sampleRoutes.php' );

$hr = new HTTPRequest;
$di = DataInterface::singleton();
$ro = NULL;
$sampleRoutes = array();

$title = array('Find a Route');
$navigation = array();

$endPoints = array($hr->getValue('musicianName'), $hr->getValue('musicianName2'));
$dataSubmitted = ! in_array(FALSE,$endPoints,TRUE);

$savedRoute = $hr->getValue('route');

$error='';
$findPath = false;

$typeToReport = array('saved route', 'saved route');

$finalTitle='';
if ( $dataSubmitted ) {
	if (empty($endPoints[0]) && ( empty($endPoints[1]) )) {
		$error = 'Enter a musician, band, album, or song!';
		$sampleRoutes = getSampleRoute(3);
	} elseif (empty($endPoints[0]) xor empty($endPoints[1])) {
		$startPoint = ! empty($endPoints[0]) ? $endPoints[0] : $endPoints[1];
		$allArtists = $di->getAll('artist',array('tostring'));
		$randArtists = array_rand($allArtists,5);
		foreach ($randArtists as $randArtist)
			$sampleRoutes[] = array('start'=>$startPoint, 'end'=>$allArtists[$randArtist]['tostring']);
	} else {
		$findPath = true;

		$nodes = array(null,null);
		$searchType = array(null,null);
		for ($i=0; $i<2; $i++){
			list($nodes[$i],$searchType[$i],$resultCount) = $di->search($endPoints[$i], TRUE, array('individual','artist','track','album'));
			$typeToReport[$i] = $searchType[$i] . ' entire field';
			if (empty($nodes[$i])) {
				list($nodes[$i],$searchType[$i],$resultCount) = $di->search($endPoints[$i], TRUE, array('individual','artist','track','album'), TRUE);
				$typeToReport[$i] = $searchType[$i] . ' match on wordbreaks';
			}
			if (empty($nodes[$i])) {
				$nodes[$i] = $di->searchAcrossFields($endPoints[$i]);
				$searchType[$i] = 'track';
				$typeToReport[$i] = 'multifield';
			}
			if (empty($nodes[$i])) {
				$fuzzyResults = $di->searchFuzzy($endPoints[$i]);
				foreach ($fuzzyResults as $key=>$value) {
					if (!empty($value)) {
						$searchType[$i]=$key;
						$nodes[$i]=$value;
						$typeToReport[$i] = 'fuzzy';
						break;
					}
				}
			}
		}

		if (empty($nodes[0]) || empty($nodes[1])) {
			error_log("No route found between {$endPoints[0]} and {$endPoints[1]}\r\n{$_SERVER['HTTP_USER_AGENT']}\r\n{$_SERVER['REMOTE_ADDR']}\r\n", $reporting_method, $reportee);
		} else {
			$ro = new RouteObject($nodes[0],$nodes[1],$searchType[0],$searchType[1]);
		}
	}
} elseif (! empty($savedRoute)) {
	$rs = new RouteSaver;
	$ro = $rs->retrieveRoute($savedRoute);
	if ($ro) {
		$ro->rewind();
		$rhStart=$ro->current();
		$fromType = $rhStart->getFromType();

		$ro->last();
		$rhEnd = $ro->current();
		$toType = $rhEnd->getToType();

		if ($fromType != 'track') {
			$element = $rhStart->getFrom();
			$endPoints[0] = implode(' ',$element);
		} else {
			$endPoints[0] = getTextDump($rhStart);
		}

		if ($toType != 'track') {
			$element = $rhEnd->getTo();
			$endPoints[1] = implode(' ',$element);
		} else {
			$endPoints[1] = getTextDump($rhEnd);
		}
	} else {
		$error="The route specified is not valid or has expired.  Please check the URL and try again.";
		reportError($error,TRUE,FALSE);
	}
} else {
	$sampleRoutes=getSampleRoute(3);
}

$artURL='';
if (($ro != NULL) && (get_class($ro)==='RouteObject')) {
	if($ro->getCount() > 0) {
		$ro->rewind();
		$artURL = $ro->current()->getAlbum()->getArtURL();
		if (empty($artURL)) {
			$ro->last();
			$artURL = $ro->current()->getAlbum()->getArtURL();
		}
	}
}

if (! empty($endPoints[0]) && (! empty($endPoints[1]) )) {
	$finalTitle = "connect ${endPoints[0]} to ${endPoints[1]}";
	$title[] = $finalTitle;
}

HTMLOutput::printHeader( $title, $navigation );
HTMLOutput::printRouteForm( $endPoints[0], $endPoints[1] , $error );

if ($findPath) {
	$printAddForm = false;
	if ( empty($nodes[0]) ) {
		HTMLOutput::printAddInfoForm('No data for ' . $endPoints[0] . '.', $endPoints[0]);
	}

	if ( empty($nodes[1]) ) {
		HTMLOutput::printAddInfoForm('No data for ' . $endPoints[1] . '.', $endPoints[1]);
	}
}

if (($ro!=NULL) && (get_class($ro)==='RouteObject')) {
	if ($ro->getCount() < 1) {
		error_log("No route found between {$endPoints[0]} and {$endPoints[1]}\r\n{$_SERVER['HTTP_USER_AGENT']}\r\n{$_SERVER['REMOTE_ADDR']}\r\n", $reporting_method, $reportee);
		HTMLOutput::printAddInfoForm('No path found between ' . $endPoints[0] . ' and ' . $endPoints[1] . '.');
	} else {
		HTMLOutput::printRoute($ro);
		if (! $savedRoute) {
			$rs = new RouteSaver();
			$savedRoute = $rs->saveRouteSession($ro);
		}
		HTMLOutput::printSendRoute($savedRoute);
		HTMLOutput::printRouteForm( $endPoints[0], $endPoints[1] , $error );
	}
}

echo '<div class="centered"><div class="left">';
foreach ($sampleRoutes as $myRoute) {
	HTMLOutput::printSampleRoute($myRoute['start'],$myRoute['end']);
}
echo '</div></div>';
HTMLOutput::printFooter(! empty($sampleRoutes));

function getTextDump(RouteElement $myRe) {
	$returnValue = $myRe->getTrack()->getToString();
	foreach ($myRe->getArtist(TRUE) as $thisArtist) {
		$returnValue .= ' ' . $thisArtist->getToString();
	}
	$returnValue .= ' ' . $myRe->getAlbum()->getToString();
	return $returnValue;
}