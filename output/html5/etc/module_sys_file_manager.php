<?php

if ( !isset( $GLOBALS[ "modulejson" ] ) || !is_array( $GLOBALS[ "modulejson" ] ) ) {
   $GLOBALS[ "modulejson" ] = [];
}

$GLOBALS[ "modulejson" ][ "sys_file_manager" ] = json_decode( '{"docrootexecutable":"ajax/sys_config/sys_file_manager_run.php","executable":"sys_file_manager","fields":[{"default":"header3","id":"label1","label":"Files","posthline":"true","role":"input","type":"label"},{"help":"This is the date and time from the file system on the server at the time you opened this window","id":"serverdate","label":"Server date","pull":"datetime","role":"input","type":"label"},{"id":"selectedfiles","label":"User file tree","role":"input","type":"ftree"},{"default":"tar","help":"You can select the compression type here.  Select <b>none</b> for a list of individual file links","id":"compression","label":"Compression type","name":"compression","role":"input","type":"listbox","values":"none~no~uncompressed tarball~tar~gzip tarball~gz~bzip2 tarball~bz2~xz tarball~xz~zipped~zip"},{"id":"status","label":"","multiple":"true","role":"output","type":"html"},{"id":"outfiles","label":"","multiple":"true","role":"output","type":"file"}],"height":"65vh","label":"Files Manager","modal":"true","moduleid":"sys_file_manager","nofcrefresh":"true","nojobcontrol":"true","noreset":"true","resetonload":"true","resource":"local","submit_label":"Download","uniquedir":"true"}' );
