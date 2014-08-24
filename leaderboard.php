<?php
require_once( 'errorHandling.php' );
require_once( 'HTMLOutput.php' );
require_once( 'DataInterface.php' );

$di = DataInterface::singleton();

HTMLOutput::printHeader( array('Leaderboard') );
HTMLOutput::printLeaderboard($di->getLeaderboard());
HTMLOutput::printFooter();

?>
