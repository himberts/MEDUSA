<?php
require_once '../vendor/autoload.php';


session_start();

if (!isset($_SESSION['first'])) {
  if (isset($_POST[ 'register' ])) {
        $reg = $_POST[ 'register' ];

        //$_SESSION['first'] = $reg;
   }
   else {
       $reg = 0;
   }

  $_SESSION['first'] = $reg;
}


$app = json_decode( file_get_contents( "/opt/genapp/mxray/appconfig.json" ) );

$client_id = $app->oauth2->google->client_id;
$client_secret = $app->oauth2->google->client_secret;

$redirect_uri = "http://" . $app->hostname . "/mxray/util/googleoauth.php";  

$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);


$client->addScope(Google_Service_Oauth2::USERINFO_PROFILE);
$client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);


$service = new Google_Service_Oauth2($client);
 if (! isset($_GET['code'])) {
 $auth_url = $client->createAuthUrl();
 	 
 echo filter_var($auth_url, FILTER_SANITIZE_URL);
 
 } else {
   $client->authenticate($_GET['code']);
   $_SESSION['access_token'] = $client->getAccessToken();
   
   $user = $service->userinfo->get();
  
   $username = $user->name;
   $user_id = $user->id;
   $user_email = $user->email;
   $user_link = $user->link;
   $user_picture = $user->picture;

   $username .= '_google';

   if ((int)$_SESSION["first"])
   {    
   	$redirect_uri = "http://" . $app->hostname . "/mxray/". "/?register=1&weloggedingoogle=1&email=$user_email&username=$username";
   }
   else
   {
	$redirect_uri = "http://" . $app->hostname . "/mxray/". "/?weloggedingoogle=1&email=$user_email&username=$username";
   }	

   header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));

unset( $_SESSION[ "first" ] );

 }

