<?php
require_once( 'HTTPRequest.php' );
require_once( 'HTMLOutput.php' );
require_once( 'DataInterface.php' );
require_once( 'errorHandling.php' );

$hr = new HTTPRequest;
$ho = new HTMLOutput;
$di = DataInterface::singleton();

$terms=$hr->getValue('terms');

$ho->printHeader(array('Search'),array('searchtips','add'));
echo "<div class=\"centered\"><div class=\"left\">";
$ho->printSearchForm($terms);

$limit = 10;

$termsSubmitted=(($terms !== FALSE) && strlen($terms)!=0);
if ($termsSubmitted) {
	list($individuals,$fieldType,$allIresults) = $di->search($terms,TRUE,array('individual'),TRUE,$limit);
	list($tracks,$fieldType,$allTresults) = $di->search($terms,TRUE,array('track'),TRUE,$limit);
	list($artists,$fieldType,$allAresults) = $di->search($terms,TRUE,array('artist'),TRUE,$limit);
	list($albums,$fieldType,$allLresults) = $di->search($terms,TRUE,array('album'),TRUE,$limit);

	$numIresults = count($individuals);
	$numTresults = count($tracks);
	$numAresults = count($artists);
	$numLresults = count($albums);

	echo "<h2>Musician Names</h2>\n";

	echo "<div class=\"noMatch\">";
	if (empty($individuals[0])) {
		echo "No matches";
		echo "</div>\n";
	} else {
		if ( $numIresults <= $allIresults ) {
			echo "Showing $numIresults of $allIresults matches";
		} else {
			echo "Showing $numIresults match";
			if ( $numIresults > 1 ) {
				echo "es";
			}
		}
		echo "</div>\n";
		echo "<ul class=\"searchMatch\">\n";
		foreach($individuals as $thisGuy) {
			$thisGuyName = $di->getByID('individual',$thisGuy);
			$bandResult = $di->getByRelatedId('artist','individual',$thisGuy);
			$bandListText='';
			if (! empty($bandResult) ) {
				$bandListResult = $di->getByID('artist',$bandResult,array(),TRUE);
				$bandList=array();
				foreach ($bandListResult as $blr) {
					$bandList[] = $blr['tostring'];
				}
				$bandListText = "(" . join(", ", $bandList) . ")";
			}
			echo "<li><a href=\"discography.php?t=i&amp;id=$thisGuy\" >{$thisGuyName[0]['tostring']}</a> $bandListText</li>\n";
		}
		echo "</ul>\n";
	}

	echo "<h2>Band Names</h2>\n";

	echo "<div class=\"noMatch\">\n";
	if (empty($artists)) {
		echo "No matches\n";
		echo "</div>\n";
	} else {
		if ( $numAresults <= $allAresults ) {
			echo "Showing $numAresults of $allAresults matches\n";
		} else {
			echo "Showing $numAresults match";
			if ( $numAresults > 1 ) {
				echo "es";
			}
		}
		echo "</div>\n";
		
		$myData = $di->getByID('artist',$artists);
		$ho->printSearchMatches('artist',$myData);
	}

	echo "<h2>Album Titles</h2>\n";

	echo "<div class=\"noMatch\">\n";
	if (empty($albums)) {
		echo "No matches\n";
		echo "</div>\n";
	} else {
		if ( $numLresults <= $allLresults ) {
			echo "Showing $numLresults of $allLresults matches\n";
		} else {
			echo "Showing $numLresults match";
			if ( $numLresults > 1 ) {
				echo "es";
			}
		}
		echo "</div>\n";

		$myData = $di->getByID('album',$albums);
		$ho->printSearchMatches('album',$myData);
	}

	echo "<h2>Song Titles</h2>\n";

	echo "<div class=\"noMatch\">\n";
	if (empty($tracks)) {
		echo "No matches\n";
		echo "</div>\n";
	} else {
		if ( $numTresults <= $allTresults ) {
			echo "Showing $numTresults of $allTresults matches\n";
		} else {
			echo "Showing $numTresults match";
			if ( $numTresults > 1 ) {
				echo "es";
			}
		}
		echo "</div>";

		$myData = $di->getByID('track',$tracks);
		$ho->printSearchMatches('track',$myData);
	}
	echo "</div></div>";

	$ho->printAddInfoForm();
	
	$messageToTrott = <<<TROTT
	Q: {$terms}
	I: $numIresults
	A: $numAresults
	L: $numLresults
	T: $numTresults
TROTT;
	error_log($messageToTrott, $reporting_method, $reportee);
}

$ho->printFooter(! $termsSubmitted);
?>