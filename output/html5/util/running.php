<?php

$appconfig = json_decode( file_get_contents( "/opt/genapp/mxray/appconfig.json" ), true );

require_once "/var/www/html/mxray/ajax/ga_db_lib.php";

function listrunning( $error_json_exit = false )
{
   global $appconfig;

   if ( !ga_db_status( ga_db_open( $error_json_exit ) ) ) {
       return false;
   }

   $runs = 
       ga_db_output(
           ga_db_find( 
               'running',
               ''
               )
       );

   foreach ( $runs as $v ) {
       $uuid = $v['_id'];
       $job = ga_db_output( ga_db_findOne( 'jobs', '', [ "_id" => $uuid ] ) );
       $pids = $v['pid'];
       echo "id:" . $job['_id'] . " module: " . $job['module'] . " user: " . $job['user'] . " started: " . date( "Y M d H:i:s T", ga_db_date_secs( $job["start"] ) ) . "\n";
       foreach ( $pids as $k2 => $v2 ) {
           echo "   where: " . $v2['where'] . " pid: " . $v2['pid'] . " what: " . $v2['what'] . "\n";

           $cmd = $appconfig[ 'resources' ][ $v2['where'] ] . " ps --ppid " . $v2['pid'];
           echo " cmd $cmd\n";
       }
   }
}

listrunning();
