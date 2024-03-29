<?php

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

$client_id = $app->oauth2->globus->client_id;

$client_secret = $app->oauth2->globus->client_secret;

$redirect_uri = ( isset( $app->oauth2->use_https ) ? "https" : "http" ) . "://" . $app->hostname . "/mxray/util/globusoauth.php";  

$scope = 'openid+email+profile+urn:globus:auth:scope:userportal.xsede.org:all';

$response_type = 'code';	 


 if (! isset($_GET['code'])) {
 $auth_url = "https://auth.globus.org/v2/oauth2/authorize?client_id=$client_id&redirect_uri=$redirect_uri&scope=$scope&response_type=$response_type";
 //header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
 echo filter_var($auth_url, FILTER_SANITIZE_URL);
 }
 else 
 {
   $code = $_GET['code'];
   $url = 'https://auth.globus.org/v2/oauth2/token';

   $sPD = array('grant_type' => 'authorization_code', 'redirect_uri' => $redirect_uri, 'code' => $code, 'client_id' => $client_id);
 
   $options_post = array(
    'http' => array(
            'method'  => 'POST',
	    'header'  => array ('Content-type: application/x-www-form-urlencoded'
	    	      	        ,'Authorization: Basic '. base64_encode("$client_id:$client_secret")
				),   
	    'content' => http_build_query($sPD)
           )
    ); 

   $context  = stream_context_create($options_post);
   $result = file_get_contents($url, false, $context);
  
   $res_json = json_decode($result, true);
 
   var_dump($result);
   echo "<br><br>";
   echo ($res_json['other_tokens']['0']['access_token']);     

////////// Access token to get Globus's user profile  /////////////////////////////////////////

  $url_token = 'https://auth.globus.org/v2/oauth2/userinfo';

  $options_use_token = array(
    'http' => array(
            'method'  => 'POST',
	    'header'  => array( 'Authorization: Bearer ' .  $res_json['access_token'] )	    
           )
  ); 

  $context_use_token  = stream_context_create($options_use_token);
  $result_use_token   = file_get_contents($url_token, false, $context_use_token);

  echo "<br>";
  echo "<br>";
  var_dump ($result_use_token);

  $res_json_use_token = json_decode($result_use_token, true);

  $user_email = $res_json_use_token['email'];
  $user_name  = $res_json_use_token['name'];
  $sub_id     = $res_json_use_token['sub'];

///// v2/api/identities resources ////////////////////////////////////

$url_token_id = "https://auth.globus.org/v2/api/identities/" . "$sub_id";


$options_use_token_id = array(
    'http' => array(
            'method'  => 'GET',
	    'header'  => array( 'Authorization: Bearer ' .  $res_json['access_token'] )	    
           )
 ); 

$context_use_token_id  = stream_context_create($options_use_token_id);
$result_use_token_id   = file_get_contents($url_token_id, false, $context_use_token_id);

echo "<br>";
echo "<br>";
var_dump ($result_use_token_id);

$res_json_use_token_id = json_decode($result_use_token_id, true);

echo "<br>";
echo "<br>";
var_dump ($res_json_use_token_id);

echo "<br>";
echo "<br>";

$user_username = $res_json_use_token_id['identity']['username'];

$user_username_edited = str_replace('@', '_', $user_username);
$user_username_edited = str_replace('.', '_', $user_username_edited);
$user_username_edited .= '_globus';		      
$user_username_split = explode("@", $user_username);
echo ( $user_username_split[0] );

$source = 0;

if (isset( $app->oauth2->only) )
  {
     echo "<br>";
     echo $app->oauth2->only[0];
     echo "<br>";
     echo ( $user_username_split[1] );
     
     foreach($app->oauth2->only as $val) 
     {
     	if ( $user_username_split[1] == $val)
	{
	   $source = 1;
	}					
     }

     if (!$source)
     {
         $redirect_uri = ( isset( $app->oauth2->use_https ) ? "https" : "http" ) . "://" . $app->hostname . "/mxray/". "/?sourcefailed=1";
	 header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));	

	 exit();
     }     
  }




if (isset( $app->oauth2->stripdomain) && ($app->oauth2->stripdomain == 'true' || $app->oauth2->stripdomain == 'yes'))
 {
   $user_username_edited = $user_username_split[0];
 }	


/////////// Access token (other tokens) to get XSEDE profile info ///////////////////////////////
$url_token_xsede = 'https://portal.xsede.org';                //

  $options_use_token_xsede = array(
    'http' => array(
            'method'  => 'POST',
	    'header'  => array( 'Authorization: Bearer ' .  $res_json['other_tokens']['0']['access_token'] )
           )
 ); 

$context_use_token_xsede  = stream_context_create($options_use_token_xsede);
$result_use_token_xsede   = file_get_contents($url_token_xsede, false, $context_use_token);

//////////////////////////////////////////////////////////////////////////////////////////////////////



if ((int)$_SESSION["first"])
{  
   $redirect_uri = ( isset( $app->oauth2->use_https ) ? "https" : "http" ) . "://"  . $app->hostname . "/mxray/". "/?register=1&weloggedinglobus=1&email=$user_email&name=$user_name&username=$user_username&username_split=$user_username_edited";
}
else
{
   $redirect_uri = ( isset( $app->oauth2->use_https ) ? "https" : "http" ) . "://" . $app->hostname . "/mxray/". "/?weloggedinglobus=1&email=$user_email&name=$user_name&username=$user_username&username_split=$user_username_edited";
}	

 header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));

unset( $_SESSION[ "first" ] );
}

