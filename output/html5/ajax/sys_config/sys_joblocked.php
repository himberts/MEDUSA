<?php

session_start(); 

require_once "/var/www/html/mxray/ajax/ga_filter.php";

$modjson = [];
$inputs_req = $_REQUEST;
$validation_inputs = ga_sanitize_validate( $modjson, $inputs_req, 'sys_joblocked' );

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

$cachecheck = "";
if ( isset( $_REQUEST[ '_cache_module' ] ) )
{
   $cachecheck = $_REQUEST[ '_cache_module' ];
}

if ( isset( $_REQUEST[ '_jobweight' ] ) )
{
   $jobweight = $_REQUEST[ '_jobweight' ];
}

if ( !isset( $_SESSION[ $window ] ) )
{
   $_SESSION[ $window ] = array( "logon" => "", "project" => "" );
}

if ( !isset( $_SESSION[ $window ][ 'udpport' ] ) ||
     !isset( $_SESSION[ $window ][ 'udphost' ] ) || 
     !isset( $_SESSION[ $window ][ 'resources' ] ) ||
     !isset( $_SESSION[ $window ][ 'submitpolicy' ] ) )
{
   $appjson = json_decode( file_get_contents( "/opt/genapp/mxray/appconfig.json" ) );
   $_SESSION[ $window ][ 'udphost'         ] = $appjson->messaging->udphostip;
   $_SESSION[ $window ][ 'udpport'         ] = $appjson->messaging->udpport;
   $_SESSION[ $window ][ 'resources'       ] = $appjson->resources;
   $_SESSION[ $window ][ 'resourcedefault' ] = $appjson->resourcedefault;
   $_SESSION[ $window ][ 'submitpolicy'    ] = $appjson->submitpolicy;
}

$policy = $_SESSION[ $window ][ 'submitpolicy' ];

session_write_close();

if ( isset( $_REQUEST[ '_submitpolicy' ] ) )
{
   $policy = $_REQUEST[ '_submitpolicy' ];
}

if ( !isset( $_SESSION[ $window ][ 'logon' ] ) ||
     !strlen( $_SESSION[ $window ][ 'logon' ] ) )
{
  if ( $policy != "all" )
  {
     echo '2';
  } else {
     echo '0';
  }
  exit();
}

$GLOBALS[ 'logon' ] = $_SESSION[ $window ][ 'logon' ];

require_once "../joblog.php";

$GLOBALS[ 'project' ] = isset( $_SESSION[ $window ][ 'project' ] ) &&
                        strlen( $_SESSION[ $window ][ 'project' ] ) ? 
                        $_SESSION[ $window ][ 'project' ] : "no_project_specified";

$dir = "/var/www/html/mxray/results/users/" . $_SESSION[ $window ][ 'logon' ] . "/" . $GLOBALS[ 'project' ];

$locked = isprojectlocked( $dir );

if ( !empty( $cachecheck ) && cache_check( $cachecheck ) ) {
    
   echo $GLOBALS[ "cached_uuid" ];
   exit;
}



if ( $locked )
{
   echo '1';
} else {
    if ( isset( $jobweight ) && $jobweight ) {
        $totalweight = totalweight() + $jobweight;
        
        if ( !isset( $appjson ) ) {
            $appjson = json_decode( file_get_contents( "/opt/genapp/mxray/appconfig.json" ) );
        }
        if ( !isset( $appjson->joblimits ) ) {
            
            echo '0';
            exit();
        }
        $limit = isset( $appjson->joblimits->default ) 
            ? ( $appjson->joblimits->default == "unlimited" ? 
                9e99 
                : $appjson->joblimits->default )
            : 0;

        
        if ( isset( $appjson->joblimits->users ) &&
             isset( $appjson->joblimits->users->{ $GLOBALS[ 'logon' ] } ) ) {
            $limit = $appjson->joblimits->users->{ $GLOBALS[ 'logon' ] } == "unlimited" 
                ? 9e99 
                : $appjson->joblimits->users->{ $GLOBALS[ 'logon' ] };
            
        } else {
            if ( isset( $appjson->joblimits->restricted ) &&
                 isset( $appjson->restricted ) ) {
                
                foreach ( $appjson->restricted as $k => $v ) {
                    
                    if ( in_array( $GLOBALS[ 'logon' ], $v ) ) {
                        
                        if ( array_key_exists( $k, $appjson->joblimits->restricted ) ) {
                            
                            $testlimit = 
                                $appjson->joblimits->restricted->{ $k } == "unlimited" 
                                ? 9e99
                                : $appjson->joblimits->restricted->{ $k };
                            
                            if ( $limit < $testlimit ) {
                                
                                $limit = $testlimit;
                            }
                        }
                    }
                }
            }
        }

        
        if ( $totalweight > $limit ) {
            
            echo "Your job limit ($limit) would be exceeded by submitting this job.  Please wait until your other jobs finish or cancel them in the job manager";
            exit();
        }
    } else {
        
    }
    echo '0';
}

exit();
