<?php
require_once('Authenticator.php');
$a = new Authenticator();
$a->logOut();
header('Location: /');
?>