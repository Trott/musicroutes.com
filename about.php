<?php 
require_once('HTMLOutput.php');
$ho=new HTMLOutput;
$ho->printHeader(array('About Music Routes'),array('faq','contact'));
$ho->printAbout();
$ho->printRouteForm();
$ho->printFooter();
?>