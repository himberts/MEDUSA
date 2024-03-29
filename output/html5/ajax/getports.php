<?php

# getports.php

# ga_getports() inspects modjson & finds ports for each needed field.
# inspects/update mongodb to get ports
# returns an object with port values for each field

define( GA_PORT_TIMEOUT_SECS, 60 );

require_once "/var/www/html/mxray/ajax/ga_db_lib.php";

function ga_getports( $modjson, $uuid, $error_json_exit = false ) {
    $portsbytype = [
        "matplotlib" => 1
        ]
        ;

    $portneeds = [];

    if ( !isset( $modjson ) ||
         !is_object( $modjson ) ||
         !isset( $modjson->{ "fields" } ) ) {
        return json_decode( '{"error":"ga_getports: invalid arguments","status":"failed"}' );
    }
    
    foreach ( $modjson->{"fields"} as $k => $v ) {
        if ( isset( $v->{'type'} ) &&
             isset( $v->{'id'} ) && 
             isset( $portsbytype[ $v->{'type'} ] ) ) {
            $portneeds[ $v->{'id'} ] = $portsbytype[ $v->{'type'} ];
        }
    }
    $portcount = array_sum( $portneeds );
    
    ;
    $results = json_decode( '{"status":"success"}' );
    if ( $portcount ) {
        ga_remove_stale_ports();
        if ( ga_has_ports( $uuid, $error_json_exit ) ) {
            return json_decode( '{"error":"ga_getports: job id ' . $uuid . ' already has assigned ports","status":"failed"}' );
        }
        $allocated = ga_get_ports_from_db( $uuid, $portcount, $error_json_exit );
        if ( count( $allocated ) != $portcount ) {
            return json_decode( '{"status":"failed","error":"get_ports_from_db did not return the expected number of ports"}' );
        }
        # assign ports to field ids
        $results->{ "_ports" } = (object)[];
        foreach ( $portneeds as $k => $v ) {
            for ( $i = 0; $i < $v; ++$i ) {
                $results->{ "_ports" }->{$k}[] = array_pop( $allocated );
            }
        }
    }
    return $results;
}

function ga_get_ports_from_db( $uuid, $count, $error_json_exit = false ) {
    if ( !$count ) {
        return [];
    }
    if ( !ga_db_status( ga_db_open( $error_json_exit ) ) ) {
        
        return [];
    }
    
    $portmin = 60000;
    
    $portmax = 65000;
    
    $allocated = [];

    for ( $port = $portmin; $port <= $portmax && count( $allocated ) < $count; ++$port ) {
        $results = ga_db_findOne( 
            'userports',
            'global',
            [ '_id' => $port ],
            [], 
            $error_json_exit
            );
        

        if ( ga_db_status( $results ) &&
             !is_null( $results['output'] )
            ) {
            
            continue;
        }
        # can allocate, record in db.
        $now = ga_db_output( ga_db_date() );
        if ( !ga_db_status(
                  ga_db_insert(
                      'userports',
                      'global',
                      # GENAPPIZE additional fields - app: windowid:
                      [
                       '_id' => $port,
                       'jobid' => $uuid,
                       'when' => $now,
                       'app'  => 'mxray'
                      ],
                      [],
                      $error_json_exit
                  )
             )
            ) {
            # another process grabbed it
            
            continue;
        }
        # we have the port
        $allocated[] = $port;
    }
    if ( count( $allocated ) != $count ) {
        
        if ( count( $allocated ) ) {
            ga_remove_ports_from_db( $uuid, $error_json_exit );
        }
        return [];
    }
    return $allocated;
}

function ga_remove_ports_from_db( $uuid, $error_json_exit = false ) {
    if ( !ga_db_status( ga_db_open( $error_json_exit ) ) ) {
        
        return false;
    }
    if ( !ga_db_status(
              ga_db_remove(
                  "userports",
                  "global",
                  [
                   'jobid' => $uuid
                  ]
              )
         ) ) {
        
        return false;
    }
    return true;
}
    
function ga_remove_stale_ports( $error_json_exit = false ) {
    if ( !ga_db_status( ga_db_open( $error_json_exit ) ) ) {
        error_log( __FILE__ . " ga_remove_stale_ports() could not open database\n", 3, "/tmp/mylog" );
        return false;
    }

    $uuids_to_remove = [];
    $uuids_checked   = [];

    # could do some more error checking here

    $now = ga_db_output( ga_db_date() );
    $results = ga_db_find( 'userports', 'global' );
    $used = ga_db_output( $results );
    foreach ( $used as $v ) {
        
        if ( isset( $uuids_checked[ $v->{"_id"} ] ) ) {
            
            continue;
        }
        $uuids_checked[ $v->{"_id"} ] = 1;
        if ( ga_db_date_secs_diff( $now, $v->{"when"} ) > GA_PORT_TIMEOUT_SECS ) {
            
            $running = ga_db_findOne( 'running', $v->{'app'}, [ '_id' => $v->{'jobid'} ], [ '_id' => 1 ] );
            
            if ( !isset( $running->{'output'} ) || isnull( $running->{'output'} ) ) {
                $uuids_to_remove[] = $v->{'jobid'};
            }
        }
    }

    

    foreach ( $uuids_to_remove as $v ) {
        ga_remove_ports_from_db( $v );
    }
    return false;
}

function ga_has_ports( $uuid, $error_json_exit = false ) {
    if ( !ga_db_status( ga_db_open( $error_json_exit ) ) ) {
        
        return [];
    }

    $results = ga_db_findOne( 
        'userports',
        'global',
        [ 'jobid' => $uuid ],
        [], 
        $error_json_exit
        );

    if ( ga_db_status( $results ) &&
         !is_null( $results['output'] )
        ) {
        return true;
    }
    return false;
}
