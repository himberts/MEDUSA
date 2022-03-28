<?php

function shutdown() {
    posix_kill(posix_getpid(), SIGHUP);
    sleep(1);
    posix_kill(posix_getpid(), SIGTERM);
}

// Do some initial processing

// Switch over to daemon mode.

if ($pid = pcntl_fork())
    return;     // Parent

// ob_end_clean(); // Discard the output buffer and close

fclose(STDIN);  // Close all of the standard
fclose(STDOUT); // file descriptors as we
fclose(STDERR); // are running as a daemon.

register_shutdown_function('shutdown');

if (posix_setsid() < 0)
    return;

if ($pid = pcntl_fork())
    return;     // Parent 


date_default_timezone_set("UTC");
$logdir = "_log/";

if ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) )
{
    error_log( date( "Y M d H:i:s T", time() ) . " : " .  $argv[ 0 ] . " : called with a REMOTE_ADDR\n", 3, "/tmp/php_errors" );
    exit;
}

$cc = 1 + 3; // logon, id, checkrunning

// it might be one less, that's if no login is specified


if ( $argc < $cc - 1 || $argc > $cc )
{
    error_log( date( "Y M d H:i:s T", time() ) . " : " .  $argv[ 0 ] . " : incorrect number of arguments $argc != $cc\n", 3, "/tmp/php_errors" );
    exit;
}

$pos = 0;

$GLOBALS[ 'logon' ] = ( $argc == $cc ) ? $argv[ ++$pos ] : "";
$id = $argv[ ++$pos ];
$_REQUEST[ '_uuid' ] = $id;
$checkrunning = $argv[ ++$pos ];


require_once "/var/www/html/mxray/ajax/joblog.php";

if ( !getmenumodule( $id ) )
{
    error_log( date( "Y M d H:i:s T", time() ) . " : " .  $argv[ 0 ] . " : could not find job $id in database\n", 3, "/tmp/php_errors" );
    exit;
}


ob_start();
if ( FALSE === ( $cmd = file_get_contents( "${logdir}_cmds_$id" ) ) )
{
   $cont = ob_get_contents();
   ob_end_clean();
   error_log( date( "Y M d H:i:s T", time() ) . " : " .  $argv[ 0 ] . " : _cmds_$id missing : $cont\n", 3, "/tmp/php_errors" );
   exit;
}


$json = json_decode( file_get_contents( "/opt/genapp/mxray/appconfig.json" ) );

$context = new ZMQContext();

$zmq_socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'mxray udp pusher');

$zmq_socket->connect("tcp://" . $json->messaging->zmqhostip . ":" . $json->messaging->zmqport );


logjobupdate( "running" );

$zmq_socket->send( '{"_uuid":"' . $id . '","_status":"running"}' );


logrunning();


$results = exec( $cmd );


//
logjobupdate( "finished", true );

logstoprunning();




if ( !$GLOBALS[ 'wascancelled' ] ) {
    $results = str_replace( "/var/www/html/mxray/", "", $results );

    ob_start();
    if ( FALSE === file_put_contents( "${logdir}_stdout_" . $_REQUEST[ '_uuid' ], $results ) ) {
        $cont = ob_get_contents();
        error_log( date( "Y M d H:i:s T", time() ) . " : " .  $argv[ 0 ] . " : error writing _stdout results\n", 3, "/tmp/php_errors" );
    }
    ob_end_clean();
    notify( 'finished' );
} else {
    notify( 'canceled' );
}

if ( $checkrunning == 1 )
{
    if( !clearprojectlock( $GLOBALS[ 'getmenumoduledir' ] ) ) {
// error ignored since there may not be job control
//      error_log( date( "Y M d H:i:s T", time() ) . " : " .  $argv[ 0 ] . " : " . $GLOBALS[ 'getmenumoduledir' ] . " : error clearprojectlock " . $GLOBALS[ 'lasterror' ] . "\n", 3, "/tmp/php_errors" );
   }
}

if ( !$GLOBALS[ 'wascancelled' ] ) {
    $zmq_socket->send( '{"_uuid":"' . $id . '","_status":"complete"}' );
    
    if ( !empty( $GLOBALS[ 'cache' ] ) ) {
        
        logcache( $id );
    }
}

function notify( $type ) {
    if ( isset( $GLOBALS[ 'notify' ] ) ) {
        switch( $GLOBALS[ 'notify' ] ) {
            case "email" : {
                if ( $doc = 
                     ga_db_output( 
                         ga_db_findOne( 
                             'users',
                             '',
                             [ "name" => $GLOBALS[ 'logon' ] ] 
                         ) 
                     )
                    ) {
                    if ( $doc[ 'email' ] ) {
                        $app = json_decode( file_get_contents( "/opt/genapp/mxray/appconfig.json" ) );
                        require_once "/var/www/html/mxray/ajax/mail.php";
                        $body = "Your job " . $GLOBALS[ 'menu' ] . " : " . $GLOBALS[ 'module' ] . " submitted on " . date( "Y M d H:i:s T", ga_db_date_secs( $GLOBALS[ 'jobstart' ] ) ) . " is now $type.\n"
                            . "Job ID: " . $_REQUEST[ '_uuid' ] . "\n"
                            ;
                        if ( $type == "finished" ) {
                            $body .= 
                                "Access your results:\n"
                                . "http://" . $app->hostname . "/mxray/?_reqlogin=1&_switch=" . $GLOBALS[ 'getmenumodule' ] . "/" . $GLOBALS[ 'getmenumoduleproject' ] . "/" . $_REQUEST[ '_uuid' ]
                                ;
                        }
                        mymail( $doc[ 'email' ], "[mxray][" . $GLOBALS[ 'menu' ] . ":" . $GLOBALS[ 'module' ] . "][$type]", $body );
                    }
                }
            }
            break;
            default : {
                 error_mail( "jobrun", "unknown notify selection " . $GLOBALS[ 'notify' ] );
            }             
            break;
        }
    }
}
