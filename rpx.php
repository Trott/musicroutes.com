<?php
require_once('errorHandling.php');
require_once('Authenticator.php');
$rpx_api_key = '4ebbbe6d929e93bd04d60abce5e3ef6f0c39f6d4';

/* STEP 1: Extract token POST parameter */
$token = $_POST['token'];
$redir = preg_match('/^\/[a-zA-Z0-9\.\/]*$/',$_GET['redir']) ? $_GET['redir'] : '/';

if(strlen($token) == 40) {//test the length of the token; it should be 40 characters

	/* STEP 2: Use the token to make the auth_info API call */
	$post_data = array('token'  => $token,
                     'apiKey' => $rpx_api_key,
                     'format' => 'json',
                     'extended' => 'false'); //Extended is not available to Basic.

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	$result = curl_exec($curl);
	if ($result == false){
		error_log('Curl error: ' . curl_error($curl) . "\nHTTP code: " . curl_errno($curl) . "\n" . var_dump($post_data),1,'rtrott@gmail.com');
	}
	curl_close($curl);

	/* STEP 3: Parse the JSON auth_info response */
	$auth_info = json_decode($result, true);

	if ($auth_info['stat'] == 'ok') {
		/* STEP 4: Use the identifier as the unique key to sign the user into your system.
		 This will depend on your website implementation, and you should add your own
		 code here. The user profile is in $auth_info.
		 */

		if (!array_key_exists('profile',$auth_info)) {
			throw new RuntimeException('No profile in auth_info');
		}

		// Cases for failure should include !isset $auth_info['profile']
		$profile = (array) $auth_info['profile'];

		if ((!array_key_exists('email',$profile)) || (!array_key_exists('name',$profile))) {
			throw new RuntimeException('Email and/or name missing from profile');
		}

		$name = (array) $profile['name'];

		if ((!array_key_exists('givenName',$name)) || (!array_key_exists('familyName',$name))) {
			throw new RuntimeException('Given name and/or family name missing from name array in profile');
		}

		$a = new Authenticator();
		$a->logIn($profile['identifier'], $profile['email'], $name['givenName'], $name['familyName']);
		header('Location: http://musicroutes.com' . $redir);
	} else {
		throw new RuntimeException('Error on authentication: '.print_r($auth_info,TRUE), $code);
	}
}else{
	header('Location: http://musicroutes.com' . $redir);
}
?>