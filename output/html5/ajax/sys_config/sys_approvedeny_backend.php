<?php

// modulejson

$modjson = json_decode( '{"fields":[{"id":"label1","label":"Approvedeny backend","role":"input","type":"label"}],"label":"Approve or deny registration request","moduleid":"sys_approvedeny_backend","prefix":"approvedeny","resource":"local"}' );

if ( isset( $_REQUEST[ '_r' ] ) ) {
    // no session, just process the request;
    header( 'Content-Type: text/html' );
    $css = <<<'EOT'
<style>
body {
background: rgb( 195,200,200 );
color: rgb( 43,29,14 );
}
</style>
<link rel="shortcut icon" href="../../favicon.ico" type="image/x-icon"/>
EOT;

    $title = 'mxray  - &#x3B1; 0.1 : ' . $modjson->label;
    $html = '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>' . "$title</title>$css<body><center><h3>$title</h3></center>";

    $r = $_REQUEST[ '_r' ];
    $aid = isset( $_REQUEST[ '_a' ] ) ? $_REQUEST[ '_a' ] : '';
    $did = isset( $_REQUEST[ '_d' ] ) ? $_REQUEST[ '_d' ] : '';

    require_once "../mail.php";

    if ( ( strlen( $aid ) && strlen( $did ) ) ||
         ( !strlen( $aid ) && !strlen( $did ) ) ) {
        $msg = "invalid approve-deny registration request from ip " . $_REQUEST[ 'REMOTE_ADDR' ];
        error_mail( "[mongodb][sys_approvedeny_backend.php][r0] $msg" );
        $html .= "Internal error</body></html>";
        echo $html;
        exit();
    }
        
    $app = json_decode( file_get_contents( "/opt/genapp/mxray/appconfig.json" ) );

    $error_msg = "<p>We encountered an internal error with this application.</p><p>The administrators have been notified of the details via email.</p><p>We apologize for any inconvenience.</p>";
    $url = "http://" . $app->hostname . "/mxray/ajax/sys_config/sys_approvedeny_backend.php?_r=$r";

    require_once "/var/www/html/mxray/ajax/ga_db_lib.php";

    if ( !ga_db_status( ga_db_open() ) ) {
        $msg = $ga_db_errors . "\n" . "url: $url\n";
        error_mail( "[mongodb][sys_approvedeny_backend.php][r0] $msg" );
        $html .= "$error_msg</body></html>";
        echo $html;
        exit();
    }
    
    if ( $doc =
         ga_db_output(
             ga_db_findOne( 
                 'users', 
                 '',
                 [ "_id" => ga_db_output( ga_db_Id( $r ) ) ]
             )
         )
        ) {
        if ( strlen( $aid ) && ( !isset( $doc[ 'approvalid' ] ) || $doc[ 'approvalid' ] != $aid ) ) {
            $msg = "invalid approve-deny registration request from ip " . $_REQUEST[ 'REMOTE_ADDR' ];
            error_mail( "[mongodb][sys_approvedeny_backend.php][r1a] $msg" );
            $html .= "Internal error</body></html>";
            echo $html;
            exit();
        }
        if ( strlen( $did ) && ( !isset( $doc[ 'denyid' ] ) || $doc[ 'denyid' ] != $did ) ) {
            $msg = "invalid approve-deny registration request from ip " . $_REQUEST[ 'REMOTE_ADDR' ];
            error_mail( "[mongodb][sys_approvedeny_backend.php][r1d] $msg" );
            $html .= "Internal error</body></html>";
            echo $html;
            exit();
        }
            
        $mailmsg = "Your request for an account on http://" . $app->hostname . "/mxray has been ";
    
        $html .= "<p>User " . $doc['name'] . "'s registration request has been ";
        $update = [];
        if ( strlen( $aid ) ) {
            $update[ '$unset' ] = [ "needsapproval" => "", "approvalid" => "", "denyid" => "" ];
            $update[ '$set' ] = [ "approved" => ga_db_output( ga_db_date() ) ];
            $html .= "<strong>Approved</strong>";
            $mailmsg .= "Approved.  You can now logon.";
        } else {
            $update[ '$unset' ] = [ "approvalid" => "", "denyid" => "" ];
            $update[ '$set' ] = [ "needsapproval" => "denied", "denied" => ga_db_output( ga_db_date() ) ];
            $html .= "<strong>Denied</strong>";
            $mailmsg .= "Denied";
        }
        $html .= ".</p> If you wish to undo this, you must <a href=http://" . $app->hostname . "/mxray>logon</a> and use the Administrator user management module</p>";

        mymail( $doc[ 'email' ], "[mxray][account status update]", $mailmsg );

        if ( !ga_db_status(
                  ga_db_update(
                      'users',
                      '',
                      $update 
                  )
             )
            ) {
            error_mail( "[mongodb][sys_approvedeny_backend.php][r2] " . $ga_db_errors );
        }

        echo $html;
        exit();
    }

    $html .= "<p>We could not find a matching request.</p><p>The administrators have been notified of the details via email.</p><p>We apologize for any inconvenience.</p>";
    $msg = "No matching request found, $url\n";
    error_mail( "[sys_approvedeny_backend.php][r3] " . $msg );
    echo $html;
    exit();
}   
