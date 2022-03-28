<?php

if ( !isset( $GLOBALS[ "modulejson" ] ) || !is_array( $GLOBALS[ "modulejson" ] ) ) {
   $GLOBALS[ "modulejson" ] = [];
}

$GLOBALS[ "modulejson" ][ "sysuserslist" ] = json_decode( '{"autosubmit":"true","docrootexecutable":"ajax/sys_config/sys_userslist.php","executable":"sysuserslist","fields":[{"id":"sysuserreport","label":"","role":"output","type":"html"}],"label":"Sysuserslist","moduleid":"sysuserslist","noreset":"true","resource":"local","submit_label":"Refresh","submitpolicy":"all","uniquedir":"on"}' );
