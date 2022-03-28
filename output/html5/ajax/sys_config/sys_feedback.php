<?php
header('Content-type: application/json');
session_start(); 

require_once "/var/www/html/mxray/ajax/ga_filter.php";

$modjson = json_decode( '{"eventlog":"true","fields":[{"colspan":4,"default":"header3","id":"label1","label":"Please give your feedback","role":"input","type":"label"},{"id":"serverdate","label":"Server date","pull":"datetime","role":"input","type":"label"},{"help":"Please verify your email address","id":"email","label":"Email address","pull":"email","required":"true","role":"input","size":"50","type":"email"},{"help":"Please repeat your email address","id":"email2","label":"Repeat email address","match":"email","pull":"email","required":"true","role":"input","size":"50","type":"email"},{"help":"Please enter a subject for this message.","id":"subject","label":"Subject","required":"true","role":"input","size":"50","type":"text"},{"checked":"true","help":"Check this if your comment is a suggestion.","id":"suggestion","label":"Suggestion","name":"level","role":"input","type":"radio"},{"help":"Check this if your comment is important to aid your work.","id":"important","label":"Important","name":"level","role":"input","type":"radio"},{"help":"Check this if your comment is critical and you can not proceeed with your work.","id":"critical","label":"Critical","name":"level","role":"input","type":"radio"},{"help":"This will give us more detailed information to better assist you. Only available if you are logged in.","id":"job1","label":"Provide a reference job(s) if available","role":"input","type":"job"},{"cols":60,"help":"Please take as much space as needed.  You can expand the text area at the bottom right.","id":"text1","label":"Your comments","required":"true","role":"input","rows":10,"type":"textarea"}],"label":"feedback","modal":"true","moduleid":"sys_feedback","navigator":"true","nojobcontrol":"true","noreset":"true","resetonload":"true","resource":"local","submitpolicy":"all"}' );
$inputs_req = $_REQUEST;
$validation_inputs = ga_sanitize_validate( $modjson, $inputs_req, 'sys_feedback' );

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
  $GLOBALS[ 'logon' ] = $_SESSION[ $window ][ 'logon' ];
  $results[ '_logon' ] = $_SESSION[ $window ][ 'logon' ];
} else {
  $results[ '_logon' ] = "";
  $results[ '_project' ] = "";
}
session_write_close();

if ( !sizeof( $_REQUEST ) )
{
    $results[ 'error' ] = "PHP code received no \$_REQUEST?";
    $results[ '_status' ] = 'failed';
    echo (json_encode($results));
    exit();
}

if ( !isset( $_REQUEST[ 'text1' ] ) || !strlen( $_REQUEST[ 'text1' ] ) )
{
//    $results[ 'error' ] = "You must provide a non-empty comment to submit feedback";
    $results[ '_message' ] = array( "icon" => "warning.png", "text" => "You must provide a non-empty comment to submit feedback" );
    $results[ '_status' ] = 'failed';
    echo (json_encode($results));
    exit();
}



require_once "../mail.php";
date_default_timezone_set( 'UTC' );
$json = json_decode( file_get_contents( "/opt/genapp/mxray/appconfig.json" ) );

$GLOBALS[ 'REMOTE_ADDR' ] = isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? $_SERVER[ 'REMOTE_ADDR' ] : "not from an ip";

// $subject =  gethostname() . "/mxray " . $_REQUEST[ 'level' ] . " feedback from " . $results[ '_logon' ] . "@" . $GLOBALS[ 'REMOTE_ADDR' ] . ( isset( $results[ '_project' ] ) ? " project " . $results[ '_project' ] : "" );

$subject =  "[" . gethostname() . "/mxray-feedback][" . $_REQUEST[ 'level' ] . "] '" . $_REQUEST[ 'subject' ] . "' " . $_REQUEST[ 'email' ];

$add = 
"\n" .
"subject : " . $_REQUEST[ 'subject' ] . "\n" .
"from    : " . $results[ '_logon' ] . "\n" .
"email   : " . $_REQUEST[ 'email' ] . "\n" .
"level   : " . $_REQUEST[ 'level' ] . "\n------------------------\n" .
$_REQUEST[ 'text1' ]
    ;


$data =
"project   : " . ( isset( $results[ '_project' ] ) ? $results[ '_project' ] : "no_project_specified" ) . "\n" .
"remote ip : " . $GLOBALS[ 'REMOTE_ADDR' ] . "\n" .
"browser   : " . $_REQUEST[ '_navigator' ] . "\n------------------------\n" .
// . "Events    : " . 
$_REQUEST[ '_eventlog' ] . "\n"
;

$ats = array( "json input" => "_args_", "command" => "_cmds_", "output" => "_stdout_", "error output" =>  "_stderr_" ); 

$attach = array();
$attachinfo = "";

$attachdata = array();

if ( isset( $_REQUEST[ 'job1_altval' ] ) && count( $_REQUEST[ 'job1_altval' ] ) ) {
    require_once "../joblog.php";

    
    foreach ( $_REQUEST[ 'job1_altval' ] as $v ) {

        if ( getmenumodule( $v ) ) {

            $attachinfo .= 
                "related job $v\n" .
                "  module : " . $GLOBALS[ "getmenumodule"        ] . "\n" .
                "  project: " . $GLOBALS[ "getmenumoduleproject" ] . "\n" .
                "  status : " . $GLOBALS[ "getmenumodulestatus"  ] . "\n" ;

            // attach log files

            $logdir = $GLOBALS[ "getmenumodulelogdir"  ];

            
            foreach ( $ats as $k1=>$v1 ) {
                $f = "$logdir/$v1$v";

                if ( file_exists( $f ) ) {
                    $attachinfo .= "  attach : $k1 as $v1$v\n";
                    $attach[] = $f;
                }
            }
        } else {
            $attachinfo .= 
                "Related job $v information not found in database\n";

        }
        $attachinfo .= "------------------------\n";
    }
    //    $add .= $attachinfo;
    $attachdata[] = 
        array(
            "data" => $attachinfo
            ,"name" => "attachmentsummary.txt" );
}

$attachdata[] = 
    array(
        "data" => $data
        ,"name" => "eventlog.txt" );


if ( mymail_attach(
         $json->mail->feedback,
         $subject,
         $add,
         $attach,
         $attachdata
     ) )
{
    $results[ 'error' ]  = "Could not send email, mail server is down or not accepting requests";
    $results[ '_status' ] = 'failed';
} else {
    $results[ '_status' ] = 'complete';
    $results[ '-close2' ] = 1;
    $results[ '_message' ] = array( 'icon' => 'information.png', 'text' => 'Your feedback has been submitted.  Thank you.' );
}

echo (json_encode($results));
exit();
