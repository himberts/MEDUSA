<?php

if ( !isset( $GLOBALS[ "modulejson" ] ) || !is_array( $GLOBALS[ "modulejson" ] ) ) {
   $GLOBALS[ "modulejson" ] = [];
}

$GLOBALS[ "modulejson" ][ "jobmonitor" ] = json_decode( '{"docrootexecutable":"ajax/sys_config/sys_jobmonitor.php","executable":"jobmonitor","fields":[{"default":5,"id":"interval","label":"Update frequency in seconds","min":5,"role":"input","type":"integer"},{"height":"150px","help":"drag to pan, double click to zoom, to reset zoom and pan: click on live coordinates box","hover":"true","id":"jobhistory","label":"Active job count","pan":"true","role":"output","type":"plot2d","width":"600px","zoom":"true"},{"height":"150px","help":"drag to pan, double click to zoom, to reset zoom and pan: click on live coordinates box","hover":"true","id":"load","label":"Load %","pan":"true","role":"output","type":"plot2d","width":"600px","zoom":"true"},{"height":"150px","help":"drag to pan, double click to zoom, to reset zoom and pan: click on live coordinates box","hover":"true","id":"iowait","label":"IO wait %","pan":"true","role":"output","type":"plot2d","width":"600px","zoom":"true"},{"height":"150px","help":"drag to pan, double click to zoom, to reset zoom and pan: click on live coordinates box","hover":"true","id":"memused","label":"Memory used %","pan":"true","role":"output","type":"plot2d","width":"600px","zoom":"true"},{"height":"150px","help":"drag to pan, double click to zoom, to reset zoom and pan: click on live coordinates box","hover":"true","id":"swapused","label":"Swap used %","pan":"true","role":"output","type":"plot2d","width":"600px","zoom":"true"},{"height":"150px","help":"drag to pan, double click to zoom, to reset zoom and pan: click on live coordinates box","hover":"true","id":"net","label":"Network MB/s","pan":"true","role":"output","type":"plot2d","width":"600px","zoom":"true"},{"id":"monitordata","label":"","role":"output","type":"html"}],"label":"Jobmonitor","moduleid":"jobmonitor","resource":"local","submitpolicy":"all","uniquedir":"on"}' );
