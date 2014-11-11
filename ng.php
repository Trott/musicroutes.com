<?php
require_once( 'HTMLOutput.php' );
require_once( 'HTTPRequest.php' );
require_once( 'DataInterface.php' );
require_once( 'RouteObject.php' );

$hr = new HTTPRequest;
$di = DataInterface::singleton();
$ro = NULL;
$sampleRoutes = array();

$endPoints = array($hr->getValue('musicianName'), $hr->getValue('musicianName2'));

$findPath = false;

$finalTitle='';

if (empty($endPoints[0]) && ( empty($endPoints[1]) )) {
  $allArtists = $di->getAll('artist',array('tostring'));
  $randStartArtists = array_rand($allArtists,5);
  $randEndArtists = array_rand($allArtists,5);
  for ($i=0; $i<5; $i++)
    $sampleRoutes[] = array('start'=>$allArtists[$randStartArtists[$i]]['tostring'], 
      'end'=>$allArtists[$randEndArtists[$i]]['tostring']
    );
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
    if (empty($nodes[$i])) {
      list($nodes[$i],$searchType[$i],$resultCount) = $di->search($endPoints[$i], TRUE, array('individual','artist','track','album'), TRUE);
    }
    if (empty($nodes[$i])) {
      $nodes[$i] = $di->searchAcrossFields($endPoints[$i]);
      $searchType[$i] = 'track';
    }
    if (empty($nodes[$i])) {
      $fuzzyResults = $di->searchFuzzy($endPoints[$i]);
      foreach ($fuzzyResults as $key=>$value) {
        if (!empty($value)) {
          $searchType[$i]=$key;
          $nodes[$i]=$value;
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

$title = '';
if (! empty($endPoints[0]) && (! empty($endPoints[1]) )) {
  $title = htmlspecialchars(" | connect ${endPoints[0]} to ${endPoints[1]}");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Music Routes<?php echo $title; ?></title>
    <link rel="stylesheet" href="style.min.css" type="text/css" />
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">   
  </head>
  <body onload="if (foo=document.getElementById('firstTextBox')) foo.focus();">
    <div class="pageContainer">
      <div class="page">
        <div class="header">
          <div class="tagline">Connect one musician to another<br />
            through the musicians they've played with
          </div>
          <a href="/" class="logoHome"><img src="images/webLogo.png" alt="Music Routes Logo" /></a>
          <a href="/" class="nameHome"><img src="images/music_routes.png" alt="Music Routes" /></a>
          <div class="navBar">
          <ul>
            <li><a href="ng.php">Find a Route</a></li>
            <li><a href="search.php">Search</a></li>
            <li><a href="add.php">Add Info</a></li>
            <li><a href="http://blog.musicroutes.com/">Blog</a></li>
          </ul>
        </div>
      </div>
      <div class="meat">
        <div class="content">
          <h1>Find a Route</h1>
          <form action="ng.php" class="compact">
            Connect <input type="text" name="musicianName" id="firstTextBox" value="<?php echo htmlspecialchars($_GET['musicianName']); ?>" class="textfield" onfocus="this.className='textfield hasfocus';" onblur="this.className='textfield';" />
            → <input type="text" name="musicianName2" value="<?php echo htmlspecialchars($_GET['musicianName2']); ?>" class="textfield" onfocus="this.className='textfield hasfocus';" onblur="this.className='textfield';" />
            <input  type="submit" class="submit compact" value="Route" onclick="document.getElementById('pleaseWait1').style.display='block';" />
          </form>
<?php
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
  }
}

echo '<div class="centered"><div class="left">';
foreach ($sampleRoutes as $myRoute) {
  ?>
  <div class="sample">Try: <a href="ng.php?musicianName=<?php echo htmlspecialchars(urlencode($myRoute['start'])) ?>&amp;musicianName2=<?php echo htmlspecialchars(urlencode($myRoute['end'])) ?>"><?php echo htmlspecialchars($myRoute['start']) ?>&nbsp;&rarr;&nbsp;<?php echo htmlspecialchars($myRoute['end']) ?></a></div>
  <?php
}
?>
          </div>    
        </div>
      </div>
    </div>

    <div class="footer">
      <div><a href="tos.php">Terms
        of Service</a> - <a href="contact.php">Contact Us</a></div>
        <div class="design">Site design by <a target="_blank" href="http://www.humuhumu.com/">Humuhumu</a></div>
      </div>

    </div>
  </div>
  <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-7422407-1', 'auto');
  ga('send', 'pageview');

  </script>
</body>
</html>
