<?php
require_once('errorHandling.php');
require_once('Authenticator.php');
require_once('UserModel.php');
require_once('RouteObject.php');
require_once('RouteElement.php');

class HTMLOutput {
	static protected $selectArray = array();
	static protected $firstTextBox = FALSE;
	static protected $pleaseWaitId = 0;
	static protected $jQueryLoaded = FALSE;
	static protected $addInfoSignInPromptPrinted = FALSE;

	private static function formatDataOutput($dataType) {
		if ($dataType==='track') {
			return array('song','&ldquo;','&rdquo;');
		}
		if ($dataType==='individual') {
			return array('musician','','');
		}
		if ($dataType==='album') {
			return array('album','<i>','</i>');
		}
		if ($dataType==='artist') {
			return array('artist','','');
		}
		if ($dataType==='guest_musician') {
			return array('guest musician','','');
		}
		if ($dataType==='guest_appearance') {
			return array('guest appearance','','');
		}
		return array($dataType,'','');
	}

	private static function generateLinks($linkString) {
		$linkString = (string) $linkString;

		switch ($linkString) {
			case 'search':
				return array('search.php', 'Search');
			case 'route':
				return array('route.php', 'Find a Route');
			case 'add':
				return array('add.php', 'Add Info');
			case 'searchtips':
				return array('searchTips.php', 'Search Tips');
			case 'contact':
				return array('contact.php', 'Contact Us');
			case 'about':
				return array('about.php', 'About');
			default:
				throw new InvalidArgumentException("Invalid link string: $linkString");
		}
	}

	public static function printSearchResultsSectionHeader($dataType) {
		list($dataTypeString,,) = self::formatDataOutput($dataType);
		self::printSectionHeader("{$dataTypeString}s");
	}

	public static function printSectionHeader($headerText) {
		echo '<h2>' . htmlspecialchars($headerText) . '</h2>';
	}

	public static function printParagraph($text) {
		echo '<p>' . htmlspecialchars($text) . '</p>';
	}

	public static function printCopyrightParagraph() {
		echo '<p>If you believe that your work has been copied and is accessible on the service in a
way that constitutes copyright infringement, you may notify 
MusicRoutes by <a href="contact.php">using our contact form</a>.</p>';
	}

	public static function printContactParagraph() {
		echo '<p>Questions? Please contact us <a href="contact.php">using our contact form</a>.</p>';
	}

	public static function printSearchMatches($tableName, $myData) {
		list(,$pre,$post)=self::formatDataOutput($tableName);
		echo '<ul class="searchMatch">' . "\n";;
		foreach ($myData as $myLine) {
			echo '<li><a href="discography.php?t=' . htmlspecialchars($tableName) . '&amp;id=' . htmlspecialchars($myLine['id']) . '">' . $pre . htmlspecialchars($myLine['tostring']) . $post . '</a></li>' . "\n";
		}
		echo '</ul>';
	}

	public static function printDiscographyTitle($title,$type='track',array $additionalData=array(),$additionalDataType='artist',$additionalDataPre='(',$additionalDataPost=')') {
		list(,$pre,$post)=self::formatDataOutput($type);
		echo '<h2 class="discographyPageTitle">' . $pre . htmlspecialchars($title) . $post;
		if (! empty($additionalData)) {
			$myData=current($additionalData);
			echo ' <span class="smaller">' . htmlspecialchars($additionalDataPre);
			list(,$pre,$post)=self::formatDataOutput("$additionalDataType");
			echo '<a href="discography.php?t=' . htmlspecialchars($type) . '&amp;id=' . htmlspecialchars($myData['id']) . '">' . $pre . htmlspecialchars($myData['tostring']) . $post . '</a>';
			while ($myData=next($additionalData)) {
				echo ', <a href="discography.php?t=' . htmlspecialchars($type) . '&amp;id=' . htmlspecialchars($myData['id']) . '">' . $pre . htmlspecialchars($myData['tostring']) . $post . '</a>';
			}
			echo htmlspecialchars($additionalDataPost) . '</span>';
		}
		echo "</h2>\n<div class=\"centered\"><dl>\n";
	}

	public static function printDiscographySection($type,array $data,$label,DiscographyElement $rootElement=null,$compact=FALSE) {
		list(,$pre,$post)=self::formatDataOutput($type);
		if (is_array($label)) {
			list(,$labelPre,$labelPost)=self::formatDataOutput($label[0]);
			if (($label[0]=='artist') && (is_array($label[1]))) {
				echo '<dt>'. self::getHtmlArtistFromArray($label[1]) . '</dt>';
			} else {
				echo '<dt><a href="discography.php?t='.htmlspecialchars($label[0]).'&amp;id='.htmlspecialchars($label[1]->getID()).'">'.$labelPre.htmlspecialchars($label[1]->getToString()).$labelPost.'</a>:</dt>';
			}
		} else {
			echo '<dt>'.htmlspecialchars($label).':</dt>';
		}
		$first = TRUE;
		foreach ($data as $myLine) {
			$extraData = '';
			if (! empty($rootElement)) {
				$extraData = $myLine->getContextSpecificData($rootElement);
				if (strlen($extraData) > 0) {
					$extraData = '(' . $extraData . ')';
				}
			}
			$content = '<a href="discography.php?t=' . htmlspecialchars($type) . '&amp;id=' . htmlspecialchars($myLine->getID()) . '">' . $pre . htmlspecialchars($myLine->getToString()) . $post . '</a> ' . htmlspecialchars($extraData);
			if ( $compact ) {
				if ($first) {
					echo '<dd>';
				} else {
					echo ' &amp; ';
				}
				echo "$content";
			} else {
				echo "<dd>$content</dd>\n";
			}
			$first = FALSE;
		}
		if ($compact && ! $first) {
			echo "</dd>\n";
		}
	}

	public static function printDiscographySectionNested(array $nest) {
		$prevArtist = null;
		list(,$trackPre,$trackPost)=self::formatDataOutput('track');
		list(,$albumPre,$albumPost)=self::formatDataOutput('album');
		list(,$artistPre,$artistPost)=self::formatDataOutput('artist');
		foreach ($nest as $item) {
			if ($prevArtist != $item[0]){
				echo '<dt>With ';
				$artists=array();
				foreach($item[0] as $artist) {
					$artists[] = '<a href="discography.php?t=a&amp;id='.htmlspecialchars($artist->getID()).'">'.$artistPre.htmlspecialchars($artist->getToString()).$artistPost.'</a>';
				}
				echo implode(' &amp; ',$artists);
				echo ':</dt>';
				$prevArtist = $item[0];
			}
			echo '<dd><dl>';
			self::printDiscographySection('track',$item[2],array('album',$item[1]));
			echo '</dl></dd>';
		}
	}

	public static function printDiscographyEnd() {
		echo "</dl></div>\n";
	}

	public static function printHeader($title=array(),$nav=array(), array $facebookMeta=array(),$bustOut=FALSE) {
		if (! is_array($title)) {
			throw new InvalidArgumentException("Title must be an array: $title");
		}
		if (! empty($title)) {
			$myHeaderString=htmlspecialchars($title[0]);
		} else {
			$myHeaderString='';
		}
		array_unshift($title,'Music Routes');
		$myTitleString = implode(' | ', $title);
		$myTitleString=htmlspecialchars($myTitleString);
		$fbTitleString = array_key_exists('title',$facebookMeta) ?
			'<meta name="title" content="' . htmlspecialchars($facebookMeta['title']) . '" />' : '';
		$fbDescriptionString = array_key_exists('description',$facebookMeta) ?
			'<meta name="description" content="' . htmlspecialchars($facebookMeta['description']) . '" />' : '';
		$fbImageString = array_key_exists('artURL',$facebookMeta) && (! empty($facebookMeta['artURL'])) ?
			'<link rel="image_src" href="'.htmlspecialchars($facebookMeta['artURL']).'" />' : '';

		echo "<!DOCTYPE html><html lang=\"en\"><head><title>$myTitleString</title>";
		self::printStyleSheet();
		echo "<meta charset=\"utf-8\" />
		<meta name=viewport content=\"width=device-width, initial-scale=1\">
		$fbTitleString
		$fbDescriptionString
		$fbImageString
		</head>
		<body onload=\"if (foo=document.getElementById('firstTextBox')) foo.focus();\">
		<table class=\"pageContainer\">
		<tr>
		<td class=\"page\">
		<div class=\"header\">
		<div class=\"tagline\">Connect one musician to another<br />
		through the musicians they've played with</div>
				<a href=\"/\" class=\"logoHome\"><img src=\"images/webLogo.png\" alt=\"Music Routes Logo\" /></a>
				<a href=\"/\" class=\"nameHome\"><img src=\"images/music_routes.png\" alt=\"Music Routes\" /></a>
				<div class=\"navBar\">
				<ul>
				<li><a href=\"route.php\">Find a Route</a></li>
				<li><a href=\"search.php\">Search</a></li>
				<li><a href=\"add.php\">Add Info</a></li>
				<li><a href=\"http://blog.musicroutes.com/\">Blog</a></li>
				</ul>
				</div>
				</div>

				<div class=\"meat\">
				<div class=\"content\"><h1>$myHeaderString</h1>";
	}

	public static function printSignIn($compact=true) {
		if ($compact) {
			echo "<li class=\"profile\"><a class=\"rpxnow\" onclick=\"return false;\" href=\"https://music-routes.rpxnow.com/openid/v2/signin?token_url=http%3A%2F%2Fmusicroutes.com%2Frpx.php?redir=".urlencode($_SERVER['PHP_SELF'])."\">Sign In</a></li>";
		} else {
			echo '<div class="centered">You must <a class="rpxnow" onclick="return false;" href="https://music-routes.rpxnow.com/openid/v2/signin?token_url=http%3A%2F%2Fmusicroutes.com%2Frpx.php?redir='.urlencode($_SERVER['PHP_SELF']).'">sign in</a> to use this feature.</div>';
		}
	}

	public static function loadJQuery() {
		if (self::$jQueryLoaded)
		return;
		echo "<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js\" type=\"text/javascript\"></script>";
		self::$jQueryLoaded = TRUE;
	}

	public static function printStyleSheet($absolute=FALSE) {
		$prefix = $absolute ? 'http://musicroutes.com/' : '';
		echo '<link rel="stylesheet" href="'. $prefix . 'style.css?v=1.1.1" type="text/css" />';
	}

	public static function printFormStart($captionText='',$errorMessage='', $action='', $compact=FALSE, $get=FALSE ) {
		if (! empty($errorMessage)) {
			$errorMessage = '<span class="inputError">' . htmlspecialchars($errorMessage) . "</span><br/><br/>\n";
		}
		$captionText = $errorMessage . htmlspecialchars($captionText);
		if (empty($action)) {
			$action = $_SERVER['SCRIPT_NAME'];
		}
		$class = $compact ? 'compact' : 'standard';
		$method = $get ? 'get' : 'post';
		echo "<form action=\"$action\" method=\"$method\" class=\"$class\">\n";
		if (! $compact ) {
			echo "<table>\n";
			if (! empty($captionText)) {
				echo "<caption>$captionText</caption>\n";
			}
		} else {
			echo $errorMessage;
		}
	}

	public static function printFormEnd($compact=FALSE,$pleaseWait=FALSE,$facebook=FALSE) {
		if (! $compact) {
			echo "</table>\n";
		}
		echo "</form>\n";
		if ( $pleaseWait ) {
			$urlStart = $facebook ? 'http://musicroutes.com/' : '';
			echo '<div id="pleaseWait'.self::$pleaseWaitId.'" class="pleaseWait">Finding route...<br/><progress></progress></div>';
		}
	}

	public static function printInputHidden($fieldname,$value) {
		$myFieldname = htmlspecialchars($fieldname);
		$myValue = htmlspecialchars($value);
		echo "<tr><td><input type=\"hidden\" name=\"$myFieldname\" value=\"$myValue\" /></td></tr>";
	}

	public static function printInputCheckboxSet($fieldname,$label,array $elements) {
		$fieldname=htmlspecialchars($fieldname);
		$label=htmlspecialchars($label);

		echo '<tr><td class="standardLabel">'.$label.'</td><td>';
		foreach ($elements as $element) {
			$myString = htmlspecialchars($element->getToString());
			$myValue = htmlspecialchars($element->getId());
			echo '<input type="checkbox" name="'.$fieldname.'" value="'.$myValue.'" checked="checked" />&nbsp;<span class="checkboxLabel">'.$myString.'</span><br/>';
		}
		echo '</td></tr>';
	}
        

	public static function printInputText($fieldname,$label='',$value='',$focus=FALSE,$readonly=FALSE,$disabled=FALSE,$compact=FALSE) {
		$label = htmlspecialchars($label);
		$fieldname = htmlspecialchars($fieldname);
		$valueText = $value ? 'value="' . htmlspecialchars($value) . '"' : '';
		if ((! self::$firstTextBox) && ($focus)) {
			$idText = 'id="firstTextBox"';
			self::$firstTextBox = TRUE;
		} else {
			$idText = '';
		}
		$readonlyText = $readonly ? 'readonly="readonly"' : '';
		$disabledText = $disabled ? 'disabled="disabled"' : '';

		if (! $compact) {
			echo "<tr>\n<td class=\"standardLabel\">";
		}
		echo "$label";
		if (! $compact) {
			echo "</td>\n<td>";
		} else {
			// space needed between label and input field when $compact=TRUE
			echo " ";
		}
		echo "<input type=\"text\" name=\"$fieldname\" $idText $readonlyText $disabledText $valueText class=\"textfield\" ";
		echo 'onfocus="this.className=\'textfield hasfocus\';" onblur="this.className=\'textfield\';" />';
		if (! $compact) {
			echo "</td></tr>\n";
		}
	}

	public static function printInputTextarea($fieldname,$label='',$value='',$focus=FALSE) {
		$label = htmlspecialchars($label);
		$fieldname = htmlspecialchars($fieldname);
		$valueText = htmlspecialchars($value);
		if ((! self::$firstTextBox) && ($focus)) {
			$idText = 'id="firstTextBox"';
			self::$firstTextBox = TRUE;
		} else {
			$idText = '';
		}
		echo "<tr>\n<td class=\"standardLabel\">$label</td>\n";
		echo "<td><textarea cols=\"35\" rows=\"4\" name=\"$fieldname\" $idText class=\"textareafield\" ";
		echo 'onfocus="this.className=\'textareafield textareahasfocus\';" onblur="this.className=\'textareafield\';">' . $valueText . '</textarea></td></tr>' . "\n";
	}

	public static function printInputSelect($fieldname,$label,$valueArray,$optionsLabelKey,$optionsValueKey,$selectedValuesArray=array()) {
		$fieldname = htmlspecialchars($fieldname);
		$label = htmlspecialchars($label);
		$valueArray = (array) $valueArray;
		$selectedValuesArray = (array) $selectedValuesArray;

		$myOptionsString = "";
		$mySelectedOptionsString = "";
		foreach ($valueArray as $myOptions) {
			if (! is_array($myOptions)) {
				throw new InvalidArgumentException('Array expected: $myOptions');
			}

			if ((! array_key_exists($optionsValueKey,$myOptions)) || (! array_key_exists($optionsLabelKey,$myOptions))) {
				throw new InvalidArgumentException('Array did not have expected keys');
			}

			$optionString = '<option value="' . htmlspecialchars($myOptions[$optionsValueKey]) . '">' . htmlspecialchars($myOptions[$optionsLabelKey]) . "</option>\n";

			if (in_array($myOptions[$optionsValueKey],$selectedValuesArray)) {
				$mySelectedOptionsString .= $optionString;
			} else {
				$myOptionsString .= $optionString;
			}
		}

		echo "<tr>\n<td>$label</td>\n<td class=\"selectWidget\">";
		if (empty(self::$selectArray)) {
			echo '<script type="text/javascript" src="selectArray.js"></script>';
		}
		self::$selectArray[] = '$fieldname';
		echo "<script type=\"text/javascript\">selectArray[selectArray.length] = '$fieldname'</script>\n";
		echo '<input type="button" value="&lt;&gt;" onclick="swapOption( \'' . $fieldname . '\', \'notselected' . $fieldname . '\')" /><br/>' . "\n";


		echo '<select id="notselected' . $fieldname . '" name="notselected' . $fieldname . '[]" multiple="multiple">' . "\n";
		echo $myOptionsString;
		echo "</select>\n";

		echo '<select id="' . $fieldname . '" name="' . $fieldname . '[]" multiple="multiple">' . "\n";
		echo $mySelectedOptionsString;
		echo "</select>\n";

		echo "</td>\n</tr>\n";
	}

	public static function printSubmitButton($label='',$image=NULL,$compact=FALSE, $pleaseWait=FALSE, $facebook=FALSE) {
		$label = htmlspecialchars($label);
		if ($pleaseWait) {
			if ($facebook) {
				$pleaseWaitText = 'clicktoshow="pleaseWait'.++self::$pleaseWaitId.'" clickthrough="true"';
			} else {
				$pleaseWaitText = 'onclick="document.getElementById(\'pleaseWait'.++self::$pleaseWaitId.'\').style.display=\'block\';"';
			}
		} else {
			$pleaseWaitText = '';
		}
		$selectAllText = empty(self::$selectArray) ? '' : 'onclick="selectAll( selectArray )"';
		if (! $compact) {
			echo '<tr><td colspan="2" class="submit">';
		}
		$class = $compact ? "compact" : "standard";

		echo "<input $selectAllText type=\"submit\" class=\"submit $class\" value=\"$label\" $pleaseWaitText />\n";

		if (! $compact ) {
			echo "</td></tr>\n";
		}
	}

	public static function printConfirmation($message, $links=array()) {
		$message = htmlspecialchars($message);
		$links = (array) $links;
		echo '<div class="centered">';
		echo $message;
		foreach ($links as $link) {
			list($location,$text) = self::generateLinks($link);
			echo "<ul><a href=\"$location\">$text</a></ul>";
		}
		echo "</div>\n";
	}

	public static function printAddInfoForm($caption='',$newArtist='',$absolute=FALSE, array $artists=array(), $album='', array $albums=array()) {
		$a = new Authenticator();
		if (! $a->isLoggedIn() ) {
			if (! self::$addInfoSignInPromptPrinted) {
				$print_caption = $caption == '' ? '' : htmlspecialchars($caption . ' ');
				echo '<p class="centered">' . $print_caption;
				echo  $myCaption . '<a class="rpxnow" onclick="return false;" href="https://music-routes.rpxnow.com/openid/v2/signin?token_url=http%3A%2F%2Fmusicroutes.com%2Frpx.php?redir=/add.php">Sign in</a> to contribute data!</p>';
				self::$addInfoSignInPromptPrinted = true;
			}
			return;
		}
		echo '<div class="centered"><div class="left">';
		$action = $absolute ? 'http://musicroutes.com/add.php' : 'add.php';
		self::printFormStart($caption,'',$action);
		$individuals=array();
		if (! empty($artists)) {
			//self::printInputCheckboxSet('existingArtist','Band(s)', $artists);
			self::printInputText('artist', 'Band/Artist', $artists[0]->getToString(), FALSE, TRUE);
			self::printInputHidden('artist_id',$artists[0]->getId());
			$individuals=$artists[0]->getRelated('individual');
		} else {
			self::printInputText('artist', 'Band/Artist', $newArtist, TRUE, FALSE);
		}
		if (! empty($individuals)) {
			self::printInputCheckboxSet('individual_id','Musicians',$individuals);
			self::printInputTextarea('personnel','Additional Musicians','',TRUE);

		} else {
			self::printInputTextarea('personnel','Musicians','',TRUE);
		}

		//TODO:  Checkbox for existing bands, with input text if we get it wrong or if there's another band involved
		//FIXME: Checkboxes for members of the band, band itself, album, maybe track too (aliases, typos, etc.?)

		//TODO: Checkboxes for collaborators?
		//TODO: Instruments
		//TODO: Google Suggest type stuff

		if (! empty($albums)) {
			self::printInputText('album','Album',$albums[0]->getToString(),FALSE,TRUE);
			self::printInputHidden('album_id',$albums[0]->getId());
		} else {
			self::printInputText('album','Album',$album,TRUE);
		}
		$track = array_key_exists('t', $_GET) ? $_GET['t'] : '';
		if (! empty($track)) {
			self::printInputText('title','Song Title',$track,FALSE,TRUE);
		} else {
			//TODO: Better way (than pipe delimiters) for entering multiple tracks?
			self::printInputText('title','Song Title',$track,TRUE);
		}
		//self::printInputTextarea('notes','Additional Notes');
		//self::printInputText('email','Your Email (optional)');
		self::printSubmitButton('Send Info','ButtonSendInfo.gif',FALSE,FALSE,$absolute);
		self::printFormEnd();
		echo '</div></div>';
	}

	public static function printSearchForm($terms='',$error='') {
		self::printFormStart('',$error,'search.php',TRUE,TRUE);
		self::printInputText('terms','',$terms,TRUE,FALSE,FALSE,TRUE);
		self::printSubmitButton('Search', 'ButtonSearch.gif', TRUE, TRUE);
		self::printFormEnd(TRUE,TRUE);
	}

	public static function printRouteForm( $from='', $to='', $error='', $action='route.php', $absolute=FALSE ) {
		self::printFormStart('',$error,$action,TRUE,TRUE);
		self::printInputText('musicianName', 'Connect', $from,TRUE,FALSE,FALSE,TRUE);
		self::printInputText('musicianName2', ' to', $to, FALSE, FALSE, FALSE, TRUE);
		self::printSubmitButton('Route', NULL, TRUE, TRUE, $absolute);
		self::printFormEnd(TRUE,TRUE,$absolute);
	}
	
	public static function printProfile( $authenticator ) {
		$um = new UserModel();
		$userProperties = $um->getUser($authenticator->getIdentifier());
		echo '<div class="centered"><div class="left">';
		echo '<h2>'.$authenticator->getName().'</h2>';
		echo '<p>You have <strong>'.$userProperties['points'].'</strong> points.</p>';
		echo '<p>You have <strong>0</strong> badges.</p>';
		echo '<p><a href="/add.php">Add information about recordings</a> to earn points and badges!</p>';
		echo '</div></div>';
	}

	public static function printBottomNote($content='') {
		if (empty($content)) {
			self::rawPrintBottomNote('Help us complete and correct our data: <a href="add.php">add info to Music Routes</a>.');
		} else {
			self::rawPrintBottomNote(htmlspecialchars($content));
		}
	}

	public static function printBottomSelfLink($msg, $values=array()) {
		$values = (array) $values;
		$destination = $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($values);
		self::rawPrintBottomNote('<a href="'.htmlspecialchars($destination).'">'.htmlspecialchars($msg).'</a>');
	}

	public static function printPadding($pixels=20) {
		$pixels = (int) $pixels;
		echo '<div style="padding-top: '.$pixels.'px"></div>';
	}

	//raw = output not put through htmlspecialchars.  Calling function must do that or otherwise supply clean input.
	//  this makes it possible to pass HTML into the function.  Note that the function is protected so unrelated objects
	//  can't pass it unsafe stuff.
	protected static function rawPrintBottomNote($content,$saveURL='') {
		echo '<div class="bottomNote">';
		self::printAddThisButton($saveURL);
		echo '</div><div class="bottomNote">'.$content.'</div>';
	}

	protected static function printRouteConnector() {
		echo '<div class="routeBridgeRight"></div><div class="routeBridgeLeft"></div>';
	}

	private static function getHtmlArtistFromArray(array $artistArray,$class='') {
		$artistPrepend = '';
		$htmlArtist = '';
		foreach ($artistArray as $artist) {
			$htmlArtist .= $artistPrepend . '<a href="discography.php?t=a&amp;id='.htmlspecialchars($artist->getID()).'" '.$class.'>'.htmlspecialchars($artist->getToString()).'</a>';
			$artistPrepend = ' &amp; ';
		}
		return $htmlArtist;
	}

	protected static function printRouteSongInfo(RouteElement $re) {
		$track = $re->getTrack();
		$rcid = $track->getRCID();
		$artistArray = $re->getArtist(TRUE);
		$album = $re->getAlbum();

		$htmlArtist = self::getHtmlArtistFromArray($artistArray);

		echo '<div class="songInfo"><div class="song">&ldquo;<a href="discography.php?id='.htmlspecialchars($track->getID()).'">'.htmlspecialchars($track->getToString()).'</a>&rdquo;</div>';
		echo '<div class="songBy">by '.$htmlArtist;
		echo ' from the album <a href="discography.php?t=l&amp;id='.htmlspecialchars($album->getID()).'" class="albumTitle">'.htmlspecialchars($album->getToString()).'</a></div>';
		echo '</div>';
	}

	protected static function printRouteArtistLeft(RouteElement $re) {
		echo '<div class="artistLeft">';
		switch ($re->getFromType()) {
			case 'artist':
				$htmlArtist = self::getHtmlArtistFromArray($re->getArtist(TRUE),'class=artist');
				echo "$htmlArtist performed";
				break;
			case 'album':
				echo '<a href="discography.php?t=l&amp;id='.htmlspecialchars($re->getAlbum()->getID()).'" class="album">'.htmlspecialchars($re->getAlbum()->getToString()).'</a> contains';
				break;
			case 'track':
				break;
			default:
				list($individual) = $re->getFrom();
				echo '<a href="discography.php?t=i&amp;id='.htmlspecialchars($individual->getID()).'" class="artist">'.htmlspecialchars($individual->getToString()).'</a> performed on';
		}
		echo '</div>';
	}

	protected static function printRouteArtistRight(RouteElement $re) {
		echo '<div class="artistRight">';
		switch ($re->getToType()) {
			case 'artist':
				$htmlArtist = self::getHtmlArtistFromArray($re->getArtist(TRUE),'class=artist');
				echo "by $htmlArtist";
				break;
			case 'album':
				echo 'on <a href="discography.php?t=l&amp;id='.htmlspecialchars($re->getAlbum()->getID()).'" class="album">'.htmlspecialchars($re->getAlbum()->getToString()).'</a>';
				break;
			case 'track':
				break;
			default:
				list($to) = $re->getTo();
				echo 'with <a href="discography.php?t=i&amp;id='.htmlspecialchars($to->getID()).'" class="artist">'.htmlspecialchars($to->getToString()).'</a>';
		}
		echo '</div>';
	}

	protected static function printRouteInstanceStart() {
		echo '<div class="routeInstance">';
	}

	protected static function printRouteInstanceEnd() {
		echo '</div>';
	}

	public static function printRoute(RouteObject $ro) {
		$ro->rewind();
		$first=TRUE;
		foreach ($ro as $key=>$routeNode) {
			if ( $first ) {
				echo "<div class=\"route\">\n";
				self::printRouteLength($ro->getCount() - 1);
				$first=FALSE;
			} else {
				self::printRouteConnector();
			}
			self::printRouteInstanceStart();
			self::printRouteArtistLeft($routeNode);
			self::printRouteSongInfo($routeNode);
			self::printRouteArtistRight($routeNode);
			self::printRouteInstanceEnd();
		}
		if (!$first) {
			echo "</div>\n";
		}
	}

	public static function printSendRoute( $saveRoute ) {
		self::rawPrintBottomNote('<p>Know how to make this route shorter? <a href="add.php">Send us the information!</a></p>',
			'http://'.$_SERVER['SERVER_NAME'].'/sendToAFriend.php?route='.urlencode($saveRoute));
	}

	public static function printRouteLength($length) {
		$length = intval($length);
		$plural = ($length === 1) ? '' : 's';
		echo "<div class=\"routeLength\">Route contains $length step$plural.</div>";
	}

	private static function printAddThisButton($emailURL='') {
		$emailButton = empty($emailURL) ?
			'<a class="addthis_button_email"></a>' :
		    '<a class="addthis_separator" href="'.$emailURL.'"><img src="http://'.$_SERVER['SERVER_NAME'].'/images/emailWidget.png" height="16" width="16" alt="Email" /></a>';

		echo '<div class="addthis_toolbox addthis_default_style" style="margin: 0 auto; display: inline-block;">
                    <a class="addthis_button_facebook"></a>
			<a class="addthis_button_twitter"></a>' . $emailButton .
                        '<a class="addthis_button_compact" href="#"></a></div>';			

		echo '<script type="text/javascript">var addthis_config = { services_exclude: "email"';
		if (! empty( $emailURL )) {
			echo ',services_custom: {name: "Email", url: "'.$emailURL.'",icon: "http://'.$_SERVER['SERVER_NAME'].'/images/emailWidget.png"}';
		}
		echo '}</script><script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js?pub=richtrott"></script>';
	}

	public static function printSampleRoute($start,$end,$target='route.php') {
		?>
<div>Try: <a href="<?php echo $target ?>?musicianName=<?php echo htmlspecialchars(urlencode($start)) ?>&amp;musicianName2=<?php echo htmlspecialchars(urlencode($end)) ?>"><?php echo htmlspecialchars($start) ?>&nbsp;to&nbsp;<?php echo htmlspecialchars($end) ?></a></div>
		<?php
	}

	public static function printFooter($specialMessage=FALSE) {
		if ($specialMessage) {
			// Put special message here if you have one...
		}
		echo'</div>
		</div>

		<div class="footer"><a href="about.php">About</a> - <a href="tos.php">Terms
		of Service</a> - <a href="contact.php">Contact Us</a><br />
		<small>&copy; 2008-2011 Rich Trott<br/>Site design by <a
		target="_blank" href="http://www.humuhumu.com/">Humuhumu</a></small></div>

		</td>
		</tr>
		</table>';
		?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
try {
var pageTracker = _gat._getTracker("UA-7422407-1");
pageTracker._trackPageview();
} catch(err) {}
</script>
<script type="text/javascript">
  var rpxJsHost = (("https:" == document.location.protocol) ? "https://" : "http://static.");
  document.write(unescape("%3Cscript src='" + rpxJsHost +
"rpxnow.com/js/lib/rpx.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
  RPXNOW.overlay = true;
  RPXNOW.language_preference = 'en';
</script>
		<?php
		echo '</body>
		</html>';
	}

	public static function printAbout() { ?>
<div class="centered"><div class="left">
	<p>
		<b>Music Routes</b> is a little hard to explain.
	</p>
	<p>But it is easy to understand!</p>
	<p>Dive in and try it!</p>
</div></div>
	<?php
	}

	private static function printMissedArtistLink($artist,$class='',$target=TRUE) {
		$myArtist = htmlspecialchars($artist);
		$myClass = empty($class) ? '' : "class=$class";
		$myTarget = $target ? 'href="/add.php?ma='.urlencode($artist).'"' : 'href="javascript:void(0)"';
		list(,$labelPre,$labelPost)=self::formatDataOutput('artist');
		echo $labelPre.'<a '.$myClass.' '.$myTarget.'>' . $myArtist . '</a>'.$labelPost;
	}

	public static function printMissedArtists(array $artists,$compact=FALSE) {
		if ($compact) {
			$limit = count($artists) < 3 ? count($artists) : 3;
			echo '<p class="centered">Add tracks by ';
			for ($i=0; $i<$limit; $i++) {
				self::printMissedArtistLink($artists[$i]);
				echo ', ';
			}
			echo '<a href="/add.php?m">more...</a></p>';
		} else {
			$limit = count($artists) < 50 ? count($artists) : 50;
			self::loadJQuery();
			echo '<div class="left-padded"><p>Select an artist or <a href="/add.php">enter one</a>.</p>';
			echo '<ul>';
			for ($i=0; $i<$limit; $i++) {
				echo '<li>';
				self::printMissedArtistLink($artists[$i],'artistLink',FALSE);
				echo '<div class="albumList"></div>';
				echo '</li>';
			}
			echo '</ul></div>';
			echo '<script type="text/javascript">$(document).ready(function() {$(".artistLink").click(function() {
			  var artist=$(this);
			  var albumList = artist.siblings(".albumList:first");	
			  if (albumList.text().length==0) albumList.load("/ajax/albumList.php?a="+encodeURIComponent(artist.text()));
			  albumList.toggle();}); });</script>';
		}
	}

	private static function printMissedAlbumLink($album) {
		$myAlbum = htmlspecialchars($album);
		list(,$labelPre,$labelPost)=self::formatDataOutput('album');
		echo '<a class="albumLink" href="javascript:void(0)">'.$labelPre.$myAlbum.$labelPost.'</a>';
	}

	public static function printMissedAlbums($artist, array $albums, $skipHeader=false) {
		$myArtist = htmlspecialchars($artist);
		if (! $skipHeader) {
			echo '<h2 class="discographyPageTitle">'.$myArtist.'</h2>';
		}
		self::loadJQuery();
		echo '<div class="centered"><div class="left">';
		echo '<p>Select an album featuring '.$myArtist.' or <a href="/add.php?a=' . urlencode($artist) . '">add a different one</a>.</p>';
		echo '<ul>';
		for ($i=0; $i<count($albums); $i++) {
			$myAlbum = trim($albums[$i]);
			if (empty($myAlbum)) continue;
			echo '<li>';
			self::printMissedAlbumLink($myAlbum);
			echo '<div class="trackList"></div>';
			echo '</li>';
		}
		echo '</ul></div></div>';
		echo '<script type="text/javascript">$(".albumLink").click(function() {
			  var album=$(this);
			  var trackList = album.siblings(".trackList:first");
			  if (trackList.text().length==0) trackList.load("/ajax/trackList.php?l="+encodeURIComponent(album.text()));
			  trackList.toggle();});</script>';
	}

	public static function printMissedTrackLink($track,$artist,$album) {
		$myTrack = htmlspecialchars($track);
		list(,$labelPre,$labelPost)=self::formatDataOutput('track');
		$myArtist = htmlspecialchars($artist);
		echo '<a href="/add.php?t=' . urlencode($track) .
			'&amp;a=' . urlencode($artist) .
			'&amp;l=' . urlencode($album) .
			'">' . $labelPre . $myTrack . $labelPost . '</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;by '.$myArtist;
	}

	public static function printMissedTracks($album, array $tracks, $skipHeader=false) {
		if (!$skipHeader) {
			$myAlbum = htmlspecialchars($album);
			list(,$labelPre,$labelPost)=self::formatDataOutput('album');
			echo '<h2 class="discographyPageTitle">'.$labelPre.$myAlbum.$labelPost.'</h2>';
		}
		//TODO: checkboxes, a select-all checkbox, a free-form text box with an "add another" button, then a submit that works, <form> tag
		echo '<ul>';
		for ($i=0; $i< count($tracks); $i++) {
			$myTrack = trim($tracks[$i]['track']);
			if (empty($myTrack)) continue;
			$myArtist = trim($tracks[$i]['artist']);
			echo '<li>';
			self::printMissedTrackLink($myTrack,$myArtist,$album);
			echo '</li>';
		}
		echo '</ul>';
	}
        
        public static function printLeaderboard(array $leaders) {
            echo "<ol>";
            foreach ($leaders as $leaderEntry) {
                echo "<li>" . $leaderEntry['name'] . " (" . $leaderEntry['points'] . ")</li>";
            }
            echo "</ol>";
        }
}
?>