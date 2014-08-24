<?php
require_once('HTMLOutput.php');
$ho = new HTMLOutput;
$ho->printHeader(array('Search Tips'),array('search','route','add'));
?>

	<h2>Search Tips</h2>
	<div>
	<p>
	Um, obviously I need to put more information on this page.
	</p><p>Got any questions I could answer here?</p><p>Oh, hey, use an asterisk (*) for wildcard searching!</p>
	</div>

<?php 
$ho->printFooter();
?>