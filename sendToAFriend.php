<?php
//TODO: Relocate HTML markup and echo statements to HTMLOutput.php. 
require_once( 'errorHandling.php' );
require_once( 'HTMLOutput.php' );
require_once( 'RouteSaver.php' );
require_once( 'HTTPRequest.php');
$ho = new HTMLOutput;
$rs = new RouteSaver;
$hr = new HTTPRequest;

list( $error, $htmlFrom, $htmlTo, $htmlComments )  = array( '', '', '', '', '' );

// Imperfect, but practical, regexps
$regExpEmail = '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i';

// Comment length limit
$commentLength = 2048;

// Did we successfully send a message?
$success = false;

//POST means send the email, GET means we're first arriving here
//POST overrides GET
$route = $hr->getValue('route');
if ($route) {
	$send = false;
}

$routePost = $hr->getValue('route',TRUE);
if ( $routePost ) {
	$route = $routePost;
	$send = true;
}

if ( ! $route ) {
	header( 'Location: /' );
	exit;
}

if ( $send ) {
	$comments = $hr->getValue('comments',TRUE);
	$htmlComments = htmlspecialchars( $comments );

	$from = $hr->getValue('from',TRUE);
	if ($from && (strlen($from) != 0)) {
		$htmlFrom = htmlspecialchars( $from );
		if ( ! preg_match( $regExpEmail, $from )) {
			$error = $error . "<p>Please check <b>Your Email Address</b> and try again.</p>\n";
		}
	} else {
		$error = $error . "<p><b>Your Email Adress</b> is required.</p>\n";
	}

	$to = $hr->getValue('to',TRUE);
	if ($to && (strlen($to) != 0)) {
		$htmlTo = htmlspecialchars( $to );
		if ( ! preg_match( $regExpEmail, $to )) {
			$error = $error . "<p>Please check <b>Recipient's Email Address</b> and try again.</p>\n";
		}
	} else {
		$error = $error . "<p><b>Recipient's Email Address</b> is required.</p>\n";
	}

	require_once('recaptchalib.php');
	$privatekey = '6LcNewIAAAAAALyielJrTQfiHyntakJjK0U8SyOQ';
	$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $hr->getValue('recaptcha_challenge_field',TRUE),$hr->getValue('recaptcha_response_field',TRUE));

	if (!$resp->is_valid) {
		$error = $error . "<p><b>ReCaptcha words incorrect!</b>  Try again!</p>\n";
	}

	$saveUrl="http://".$_SERVER['SERVER_NAME']."/route.php?route=$route";

	if ( ! $error ) {
		$routeSaved=$rs->saveRoute($route);
		if (!$routeSaved) {
			$error='An error occurred, preventing your route from being saved.';
			reportError('An error occurred preventing a route from being saved', TRUE,FALSE);
		}
	}

	if ( ! $error ) {
		$headers = $headers = "From: $from\r\n" .
			"Reply-To: $from\r\n" .
			"Bcc: rtrott@gmail.com\r\n" .
			"X-Originating-IP: {$_SERVER['REMOTE_ADDR']}\r\n" .
			'X-Mailer: PHP/' . phpversion();
		$message = "$from has sent you the following route from musicroutes.com:\r\n\r\n" .
			"\t$saveUrl\r\n";
		if ( strlen( $comments )) {
			$comments = substr( $comments, 0, 2048 );
			$message = $message . "\r\nTheir comment to you:\r\n\r\n==========\r\n$comments\r\n==========";
		}
		$message = wordwrap($message, 70);
		if (mail($to, "$from has sent you a music route!", $message, $headers)) {;
		$success = true;
		} else {
			reportError( 'mail error in sendToAFriend.php' );
			exit;
		}
	}
}

$ho->printHeader(array('Send To A Friend'),array('about','faq'));
if ( $success ) { ?>
<div>
<p>Your message has been sent.</p>
<p><a href="route.php?route=<?php echo $route ?>">Return to your route!</a></p>
</div>
<?php } else { ?>
<div><?php echo $error ?></div>

<form action="<?php echo $_SERVER['SCRIPT_NAME']?>" method="post"
	class="standard"><input type="hidden" name="route"
	value="<?php echo $route ?>" />
<table>
	<tr>
		<td class="standardLabel">*Your Email Address:</td>
		<td><input type="text" name="from" id="firstTextBox" class="textfield"
			onfocus="this.className='textfield hasfocus';"
			onblur="this.className='textfield';" value="<?php echo $htmlFrom ?>" /></td>
	</tr>
	<tr>
		<td class="standardLabel">*Recipient's Email Address:</td>
		<td><input type="text" name="to" class="textfield"
			onfocus="this.className='textfield hasfocus';"
			onblur="this.className='textfield';" value="<?php echo $htmlTo ?>" /></td>
	</tr>
	<tr>
		<td class="standardLabel">Comments:</td>
		<td><textarea rows="10" cols="72" name="comments"
			class="textareafield"
			onfocus="this.className='textareafield textareahasfocus';"
			onblur="this.className='textareafield';"><?php echo $htmlComments ?></textarea></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><?php
		require_once('recaptchalib.php');
		$publickey = '6LcNewIAAAAAAHZlFqNspGg_H-g4bIp3V_iCih5r';
		echo recaptcha_get_html($publickey);
		?></td>
	</tr>
	<tr>
		<td colspan="2" class="submit"><input type="image"
			src="images/ButtonSendRoute.gif" alt="Send Route" /></td>
	</tr>
</table>
</form>
		<?php
}

$ho->printFooter();
?>