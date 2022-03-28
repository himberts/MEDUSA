<?php
header('Content-type: application/json');
session_start(); 

require_once "/var/www/html/mxray/ajax/ga_filter.php";

$modjson = json_decode( '{"fields":[{"colspan":4,"default":"header3","id":"label1","label":"Please give your feedback","role":"input","type":"label"},{"help":"Please verify your email address","id":"email","label":"Email address","pull":"email","required":"true","role":"input","size":"50","type":"email"},{"help":"Please repeat your email address","id":"email2","label":"Repeat email address","match":"email","pull":"email","required":"true","role":"input","size":"50","type":"email"},{"help":"Please enter a subject for this message.","id":"subject","label":"Subject","required":"true","role":"input","size":"50","type":"text"},{"checked":"true","help":"Check this if your comment is a suggestion.","id":"suggestion","label":"Suggestion","name":"level","role":"input","type":"radio"},{"help":"Check this if your comment is important to aid your work.","id":"important","label":"Important","name":"level","role":"input","type":"radio"},{"help":"Check this if your comment is critical and you can not proceeed with your work.","id":"critical","label":"Critical","name":"level","role":"input","type":"radio"},{"cols":60,"help":"Please take as much space as needed.  You can expand the text area at the bottom right.","id":"text1","label":"Your comments","role":"input","rows":10,"type":"textarea"}],"label":"feedback","modal":"true","moduleid":"sys_feedback2","nojobcontrol":"true","noreset":"true","resetonload":"true","resource":"local","submitpolicy":"all"}' );
$inputs_req = $_REQUEST;
$validation_inputs = ga_sanitize_validate( $modjson, $inputs_req, 'sys_feedback2' );

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

if ( isset( $_SESSION[ $window ][ 'project' ] ) )
{
  $results[ '_project' ] = $_SESSION[ $window ][ 'project' ];
} else {
  $results[ '_project' ] = "";
}
if ( isset( $_SESSION[ $window ][ 'logon' ] ) )
{ 
  $results[ '_logon' ] = $_SESSION[ $window ][ 'logon' ];
} else {
  $results[ '_logon' ] = "";
  $results[ '_project' ] = "";
}
session_write_close();

if ( !sizeof( $_REQUEST ) )
{
    $results[ 'error' ] = "PHP code received no \$_REQUEST?";
    echo (json_encode($results));
    exit();
}

$results[ '_status' ] = 'complete';
$results[ 'request' ] = print_r( $_REQUEST, true );
echo (json_encode($results));
exit();
