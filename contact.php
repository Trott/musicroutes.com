<?php
require_once( 'errorHandling.php' );
require_once( 'HTMLOutput.php' );
$ho = new HtmlOutput();
$ho->printHeader(array('Contact Us'),array('about','faq'));
?>
<div class="centered"><div class="left">
<?php
if ((isset( $_POST['comments'] )) && (strlen( $_POST['comments'] ))) {
	$headers = $headers = 'From: rtrott@gmail.com' . "\r\n" .
       'Reply-To: rtrott@gmail.com' . "\r\n" .
       'X-Mailer: PHP/' . phpversion();
	$message = "IP: " . $_SERVER['REMOTE_ADDR'] . "\n\n";
	foreach ( $_POST as $key=>$value ) {
		$message = $message . "$key: $value\n\n";
	}
	$message = wordwrap($message, 70);
	if (mail('rtrott@gmail.com', '[musicroutes]', $message, $headers)) {
		$ho->printParagraph( "Thanks for the comment!" );
	} else {
		$ho->printParagraph( "A problem was encountered.  Please try again later.\n" );
	}
} else {
	$ho->printFormStart();
	$ho->printInputText('email','Your Email Address','',TRUE);
	$ho->printInputTextArea('comments','Comments');
	$ho->printSubmitButton('Send Info');
	$ho->printFormEnd();
}
?>
</div></div>
<?php 
$ho->printFooter();
?>