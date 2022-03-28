<?php
session_start(); 

require_once "/var/www/html/mxray/ajax/ga_filter.php";

$modjson = json_decode( '{"executable":"sys_job_manager","fields":[{"default":"header3","id":"label1","label":"Jobs","role":"input","type":"label"},{"help":"This is the date and time from the server at the time you opened this window","id":"serverdate","label":"Server date","pull":"datetime","role":"input","type":"label"},{"help":"right click on a column header to sort","id":"jobgrid","role":"input","type":"grid","url":"ajax/sys_config/sys_jobs.php"},{"checked":"true","help":"Selecting this will refresh the job state table above.","id":"refresh","label":"Refresh","name":"action","norow":"on","role":"input","type":"radio"},{"help":"Selecting this will cancel a running job or jobs selected above.","id":"cancel","label":"Cancel","name":"action","norow":"on","role":"input","type":"radio"},{"help":"Selecting this will clear the lock on a running job or jobs selected above. Use with caution, as if the job is really still running you could clobber the results by running another job in that directory.","id":"clearlock","label":"Clear Lock","name":"action","norow":"on","role":"input","type":"radio"},{"id":"removejob","label":"Remove Job","name":"action","norow":"on","role":"input","type":"radio"},{"id":"reattach","label":"Reattach","name":"action","role":"input","type":"radio"},{"cols":60,"id":"messages","label":"Messages","role":"output","rows":8,"type":"textarea"}],"height":"65vh","label":"Submitted Jobs","modal":"true","moduleid":"sys_job_manager","nojobcontrol":"true","noreset":"true","resetonload":"true","resource":"local","submit_label":"Submit"}' );
$inputs_req = $_REQUEST;
$validation_inputs = ga_sanitize_validate( $modjson, $inputs_req, 'sys_job_manager' );

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

$results[ '_status' ] = 'complete';

if ( !isset( $_SESSION[ $window ][ 'logon' ] ) )
{
  echo (json_encode($results));
  exit();
}
session_write_close();

date_default_timezone_set("UTC");

$GLOBALS[ 'logon' ] = $_SESSION[ $window ][ 'logon' ];
$GLOBALS[ 'project' ] = isset( $_REQUEST[ '_project' ] ) ? $_REQUEST[ '_project' ] : 'not in a project';
$GLOBALS[ 'REMOTE_ADDR' ] = isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? $_SERVER[ 'REMOTE_ADDR' ] : "not from an ip";

require_once "../joblog.php";

if ( !sizeof( $_REQUEST ) )
{
    $results[ "error" ] = "PHP code received no \$_REQUEST?";
    echo (json_encode($results));
    exit();
}

// error_log( print_r( $_REQUEST, true ), 3, "/tmp/mylog" );

$jobs = array();

foreach ($_REQUEST as $k => $v ) 
{
    if ( substr( $k, 0, 12 ) == "jqg_jobgrid_") 
    {
        $jobs[] = substr( $k, 12 );
    }
}

// error_log( print_r( $jobs, true ), 3, "/tmp/mylog" );

if ( !isset( $_REQUEST[ 'action' ] ) )
{
    $results[ "error" ] = "PHP code received no specified ACTION?";
    echo (json_encode($results));
    exit();
}

$anydone = false;

$results[ 'messages' ] = "";

switch( $_REQUEST[ 'action' ] )
{
    case 'refresh' : 
       $anydone = true;
       break;
    case 'cancel' : 
        if ( !sizeof( $jobs ) ) {
            $results[ "error" ] = "Cancel requires at least one selected job";
            $anydone = true;
        } else {
            $results[ 'messages' ] = $GLOBALS[ jobcancel( $jobs ) ? 'lastnotice' : 'lasterror' ];
            $anydone = true;
        }
        break;
    case 'clearlock' : 
       if ( !sizeof( $jobs ) )
       {
          $results[ "error" ] = "Clear lock requires at least one selected job";
          $anydone = true;
       } else {
          $projectdirs = array();
          foreach ( $jobs as $v )
          {
             if ( getprojectdir( $v ) )
             {
                 $projectdirs[ $GLOBALS[ 'getprojectdir' ] ] = true;
             } else {
                 $results[ 'messages' ] .= "Could not find project in database for id '$v'.\n";
                 $anydone = true;
             }
          }
//          error_log( print_r( $projectdirs, true ), 3, "/tmp/mylog" );
          foreach ( $projectdirs as $k => $v )
          {
             $dk = end( explode( "/", $k ) );
             if ( clearprojectlock( $k ) )
             {
                 $results[ 'messages' ] .= "Cleared project '$dk'.\n";
             } else {
                 $results[ 'messages' ] .= "Could NOT clear project '$dk': " . $GLOBALS[ 'lasterror' ] . $GLOBALS[ 'lasterror' ] . $GLOBALS[ 'lastnotice' ] . "\n";
             }
             $anydone = true;
          }
       }

       break;
    case 'removejob' : 
       if ( !sizeof( $jobs ) )
       {
          $results[ "error" ] = "Remove job requires at least one selected job";
          $anydone = true;
       } else {
          foreach ( $jobs as $v )
          {
             if ( getmenumodule( $v ) &&
                  ( $GLOBALS[ "getmenumodulestatus" ] == "started" ||
                    $GLOBALS[ "getmenumodulestatus" ] == "running" ) ) {
                 $results[ 'messages' ] .= "Job $v is not finished, cancel this job first\n";
             } else {
                 if ( removejob( $v ) )
                 {
                     $results[ 'messages' ] .= "Removed job $v.\n";
                 } else {
                     $results[ 'messages' ] .= "Error removing job $v: " . $GLOBALS[ 'lasterror' ] . "\n";
                 }
             }
             $anydone = true;
          }
       }
       break;
    case 'reattach' : 
       if ( sizeof( $jobs ) != 1 )
       {
          $results[ "error" ] = "Reattach must only be performed with exactly one job selected and you selected " . sizeof( $jobs );
       } else {
          if ( !getmenumodule( $jobs[0] ) )
          {
              $results[ "error" ] = "Reattach could not find job $v information.\n";
          } else {
              error_log( $GLOBALS[ "getmenumodule" ], 3, "/tmp/mylog" );
              $results[ "_switch" ] = $GLOBALS[ "getmenumodule" ] . "/" . $GLOBALS[ "getmenumoduleproject" ] . "/" . $jobs[ 0 ];
              $results[ "-close" ] = true;
              session_start(); 
              $_SESSION[ $window ][ "project" ] = $GLOBALS[ "getmenumoduleproject" ];
              session_write_close();
          }
       }
       $anydone = true;
       break;
    default : 
       $results[ "error" ] = "PHP received an unsupported ACTION " . $_REQUEST[ 'action' ];
       $anydone = true;
       break;
}

if ( !$anydone )
{
    $results[ 'error' ] = "Nothing done";
}

echo json_encode( $results );
exit();
