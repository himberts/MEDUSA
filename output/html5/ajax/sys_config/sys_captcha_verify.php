<?php
/*
 * sys_captcha_verify.php
 *
 * verify captcha info
 *
 */

$results = [];

if ( !sizeof( $_REQUEST ) ) {
    $results[ "error" ] = "PHP code received no \$_REQUEST?";
    echo (json_encode($results));
    exit();
}

require_once "/var/www/html/mxray/ajax/ga_filter.php";

$modjson = [];
$inputs_req = $_REQUEST;
$validation_inputs = ga_sanitize_validate( $modjson, $inputs_req, 'sys_captcha_verify' );

if ( $validation_inputs[ "output" ] == "failed" ) {
    $results = array( "error" => $validation_inputs[ "error" ] );
#    $results[ '_status' ] = 'failed';
#    echo ( json_encode( $results ) );
#    exit();
};

if ( 
    !isset( $_REQUEST[ "_window" ] ) ||
    !isset( $_REQUEST[ "id" ] ) ||
    !isset( $_REQUEST[ "captcha" ] )
    ) {
    $results[ 'error' ] = "Error in call";
    echo json_encode( $results );
    exit();
}

require_once "/var/www/html/mxray/ajax/ga_db_lib.php";
$now = ga_db_output( ga_db_date() );

ga_db_open( true );

$id      = $_REQUEST[ "id" ];
$captcha = $_REQUEST[ "captcha" ];

if ( $doc = ga_db_output( ga_db_findOne( 'captcha', '', [ "_id" => $id  ] ) ) ) {
    $expires = ga_db_date_add_secs( $doc[ 'time' ], 3 * 60 );

    if ( $now < $expires &&
         $doc[ 'captcha' ] == $captcha &&
         $doc[ 'window' ] == $_REQUEST[ '_window' ] ) {
        ga_db_update( 'captcha', '', [ "_id" => $id  ], [ '$set' => [ 'success' => 1 ] ], [], true );
        $results[ 'success' ] = 1;
    } else {
        ga_db_remove( 'captcha', '', [ "_id" => $id  ], [], true );
    }
}

echo json_encode( $results );
exit();

