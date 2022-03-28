<?php
header('Content-type: application/json');

session_start(); 

require_once "/var/www/html/mxray/ajax/ga_filter.php";

$modjson = [];
$inputs_req = $_REQUEST;
$validation_inputs = ga_sanitize_validate( $modjson, $inputs_req, 'cache_remove_one' );

if ( $validation_inputs[ "output" ] == "failed" ) {
    $results = array( "error" => $validation_inputs[ "error" ] );
#    $results[ '_status' ] = 'failed';
#    echo ( json_encode( $results ) );
#    exit();
};

$window = "";
if ( isset( $_REQUEST[ '_window' ] ) )
{
   $window = $_REQUEST[ '_window' ];
}
if ( !isset( $_SESSION[ $window ] ) )
{
   $_SESSION[ $window ] = array( "logon" => "", "project" => "" );
}

if ( !isset( $_SESSION[ $window ][ 'logon' ] ) ||
     !isset( $_REQUEST[ '_logon' ] ) )
{
    $results[ 'error' ] .= "Not logged in. ";
    echo (json_encode($results));
    exit();
}

if ( !isset( $_REQUEST[ "_cachedelete" ] ) ||
     !isset( $_REQUEST[ "_uuid" ] ) ) {
    $results[ 'error' ] .= "Malformed request. ";
    echo (json_encode($results));
    exit();
}
    
require_once "/var/www/html/mxray/ajax/ga_db_lib.php";
ga_db_open( true );

if ( strlen( $_REQUEST[ "_logon" ] ) ) {
    $appconfig = json_decode( file_get_contents( "/opt/genapp/mxray/appconfig.json" ) );
    $ourperms = [];
    if ( isset( $appconfig->restricted ) ) {
        foreach ( $appconfig->restricted as $k => $v ) {
            if ( in_array( $_REQUEST[ "_logon" ], $v ) ) {
                $ourperms[ $k ] = 1;
            }
        }
    }
    if ( !array_key_exists( $_REQUEST[ "_cachedelete" ], $ourperms ) ) {
        $results[ 'error' ] .= "Not authorized";
        echo (json_encode($results));
        exit();
    }        
    
    if ( !ga_db_status(
              ga_db_remove( 
                  'cache',
                  '',
                  [ "jobid" => $_REQUEST[ "_uuid" ] ],
                  [ "justOne" => true ]
              )
         )
        ) {
        $results[ 'error' ] .= "Could not remove request job from cache";
        echo (json_encode($results));
        exit();
    }        

    $results[ 'success' ] = "true";

    
}

echo (json_encode($results));
