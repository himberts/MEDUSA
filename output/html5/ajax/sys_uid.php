<?php
header('Content-type: application/json');
// $cstrong = true;
$json = json_decode( file_get_contents( "/opt/genapp/mxray/appconfig.json" ) );
// $response[ '_sid' ] = bin2hex( openssl_random_pseudo_bytes ( 20, $cstrong ) );
$response[ '_ws'  ] = 'ws://' . $json->hostip . ':' . $json->messaging->wsport;
$response[ '_ws'  ] = 'wss://' . $json->hostname . ':' . $json->messaging->wssport . "/wss2";


$response[ '_airavata' ] = [];
if ( isset( $json->resources ) &&
     isset( $json->resources->airavata ) &&
     isset( $json->resources->airavata->resources ) ) {
    $response[ '_airavata' ][ 'resources' ] = [];
    
    if ( isset( $json->resourcedefault ) && $json->resourcedefault == 'airavata' ) {
        $response[ '_airavata' ][ 'default' ] = true;
    }

    if ( isset( $json->resources->airavata->select ) && $json->resources->airavata->select != "false" ) {
        $response[ '_airavata' ][ 'select' ] = $json->resources->airavata->select;
    }

    foreach ( $json->resources->airavata->resources as $v ) {
        if ( isset( $v->host ) && !isset( $v->enabled ) || $v->enabled == true ) {
            $response[ '_airavata' ][ 'resources' ][] = array( $v->host => isset( $v->description ) ? $v->description : $v->host );
        }
    }
}

require_once "../airavata/modulesUtils.php";

$module = getModulesNames();

$response[ 'moduleinfo' ] = []; 

//$fileStr = '';
//$fileStr = ""; 

//$toeval = array();

foreach ($module as $currentmodule){

   $mj = '/var/www/html/mxray/etc/module_' . $currentmodule . '.php';
      
   if (file_exists($mj)) 
   {   
       $contents = file_get_contents($mj);
       $pos = strpos($contents, '"multistage"');

       if ($pos === false) continue;

       require_once $mj;
   
	//$fileStr .= 'require_once "' . $mj . '";';
   	//$fileStr .= "require_once \"" . $mj . "\";";
  	//$fileStr = 'require_once "' . $mj . '";';
   	//$fileStr = 'require ("' . $mj . '");';
   	//array_push($toeval,$fileStr);	
  

	foreach ( $GLOBALS[ 'modulejson' ][ $currentmodule ]->fields as $v ) {
        	if ( isset( $v->multistage ) ){
	    	//$response[ 'moduleinfo' ][ $currentmodule ] = json_decode( '$v->multistage' );
	    	$response[ 'moduleinfo' ][ $currentmodule ] = $v->multistage;
		}
  	}
    }
}	
//eval($fileStr);
//for ($i = 0; $i < count($toeval); ++$i) {
//        eval($toeval[$i]);
//   }

echo (json_encode($response));
