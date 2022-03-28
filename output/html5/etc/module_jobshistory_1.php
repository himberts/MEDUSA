<?php

if ( !isset( $GLOBALS[ "modulejson" ] ) || !is_array( $GLOBALS[ "modulejson" ] ) ) {
   $GLOBALS[ "modulejson" ] = [];
}

$GLOBALS[ "modulejson" ][ "jobshistory_1" ] = json_decode( '{"docrootexecutable":"util/jobs_history_web.php","executable":"jobshistory_1","fields":[{"help":"Note: the time for the start date is at 0h (12 am)","id":"input1","label":"Start Date","required":"true","role":"input","type":"date"},{"help":"Note: the time for the end date is at 24h (midnight)","id":"input2","label":"End Date","required":"true","role":"input","type":"date"},{"id":"jobshisreport","label":"","role":"output","type":"html"}],"label":"JobsHistory_1","moduleid":"jobshistory_1","resource":"local","submitpolicy":"all","uniquedir":"on"}' );
