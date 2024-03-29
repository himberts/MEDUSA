<?php
    ;

$debug = 0;
$debug2file = "/tmp/seedmelabsync.log";

$notes = <<<__EOD
  usage: php $argv[0] {-r} --user username --project project_name
    copies data from fs to seedmelab 
  option:
    -r     remove files and directories present on seedmelab but not present on fs

__EOD;

$options = getopt(
    "r"
    ,[
        "user:"
        ,"project:"
    ]
    );

# debugecho( json_encode( $options, JSON_PRETTY_PRINT ), 0 );

if ( !isset( $options[ "user" ] ) || !strlen( $options[ "user" ] ) ) {
    echo $notes;
    exit( 1 );
}

$results = (object) [];

$user     = $options[ "user" ];
$project  = isset( $options[ "project" ] ) ? $options[ "project" ] : "";

# setup variables

$secrets = json_decode( file_get_contents( "__secrets__" ) );

if ( $secrets == NULL ) {
    $results->_message = [ "icon" => "toast.png",
                               "text" => "<p>Could not load configuration information to setup seedmelab execution.</p>"
                               . "<p>This is a configuration error which should be forwarded to the site administrator.</p>" 
                               . "<p>seedmelab synchronization will not work this is fixed.</p>" 
        ];
    $results->error = "seedmelab configuration failed";
    $results->_status = 'failed';
    echo json_encode( $results );
    exit();
}

if ( !isset( $secrets->seedmelab ) ) {
    $results->_message = [ "icon" => "toast.png",
                               "text" => "<p>Configuration information missing 'seedmelab' definition.</p>"
                               . "<p>This is a configuration error which should be forwarded to the site administrator.</p>" 
                               . "<p>seedmelab synchronization will not work this is fixed.</p>" 
        ];
    $results->error = "Configuration missing 'seedmelab' section";
    $results->_status = 'failed';
    echo json_encode( $results );
    exit();
}

if ( !isset( $secrets->seedmelab->apikey ) ) {
    $results->_message = [ "icon" => "toast.png",
                               "text" => "<p>Configuration information missing 'seedmelab:apikey' definition.</p>"
                               . "<p>This is a configuration error which should be forwarded to the site administrator.</p>" 
                               . "<p>seedmelab synchronization will not work this is fixed.</p>" 
        ];
    $results->error = "Configuration missing 'seedmelab:apikey' definition";
    $results->_status = 'failed';
    echo json_encode( $results );
    exit();
}

if ( !isset( $secrets->seedmelab->host ) ) {
    $results->_message = [ "icon" => "toast.png",
                               "text" => "<p>Configuration information missing 'seedmelab:host' definition.</p>"
                               . "<p>This is a configuration error which should be forwarded to the site administrator.</p>" 
                               . "<p>seedmelab synchronization will not work this is fixed.</p>" 
        ];
    $results->error = "Configuration missing 'seedmelab:host' definition";
    $results->_status = 'failed';
    echo json_encode( $results );
    exit();
}

$apikey = $secrets->seedmelab->apikey;
$host = $secrets->seedmelab->host;

$fs = "/var/www/html/mxray/results/users/$user";

# setup foldershare process

$process = proc_open("/var/www/html/mxray/seedmelab/foldershare --host $host --masquerade $user --apikey $apikey",
                  [
                   [ "pipe","r" ],
                   [ "pipe","w" ],
                   [ "pipe","w" ]
                  ],
                  $pipes);

if ( !is_resource($process) ) {

    echo "Error, process is not resource\n";
    exit;
}

stream_set_blocking( $pipes[0], false );
stream_set_blocking( $pipes[1], false );
stream_set_blocking( $pipes[2], false );

# setup command stack to extract seedmelab file listing

$towrite   = [];
$waitfor   = [];
$responses = "";
$lastcmd   = "";
$waitone   = 0;
$g_info    = 
    [
     "seedmelab" => [
         "files" => []
         ,"dirs" => []
     ]
     ,"fs" => [
         "files" => []
         ,"dirs" => []
     ]
    ];


function debugecho ( $str, $level = 1 ) {
    global $debug;
    global $debug2file;
    if ( $debug >= $level ) {
        if ( isset( $debug2file ) ) {
            error_log( "$str\n", 3, $debug2file );
        } else {
            echo $str . "\n";
        }
    }
}
    
# process individual commands

function nextcmd() {
    global $cmds;
    global $towrite;
    global $waitfor;
    global $responses;
    global $lastcmd;
    global $waitone;
    global $g_info;
    
    debugecho( json_encode( $cmds, JSON_PRETTY_PRINT ) );
    
    if ( strlen( $lastcmd ) ) {
        if ( $waitone ) {
            $waitone--;
        } else {
            $basedir = preg_replace( '/^(ls -l|stat)\s*/', '', $lastcmd );
            if ( substr( $basedir, 0, 1 ) == "'" &&
                 substr( $basedir, -1, 1 ) == "'" ) {
                $basedir = substr( $basedir, 1 );
                $basedir = substr( $basedir, 0, -1 );
            }
            $basedir .= "/";
            debugecho(  "last command was $lastcmd, responses:--\n" . $responses . "\n--" );
            debugecho( "basedir is '$basedir'" );
            switch( substr( $lastcmd, 0, 3 ) ) {
                case "ls " : {
                    $files = explode( "\n", $responses );
                    array_shift( $files );
                    array_pop( $files );
                    debugecho( "files:--\n" . implode( "\n", $files ) . "\n--" );
                    # convert to filenames list
                    $files = preg_replace( '/^.*\w{3}\s\d{2}\s\w{4}\s\d\d:\d\d (.*)$/',
                                           $basedir . '$1',
                                           $files );
                    debugecho( "files after replace:--\n" . implode( "\n", $files ) . "\n--" );
                    # push commands
                    foreach ( $files as $value ) {
                        $cmds[] = [ "runcmd" => "stat '$value'" ];
                        $cmds[] = [ "waitfor" => "foldershare> " ];
                    }
                }
                break;

                case "sta" : {
                    # check response FileType:
                    # if Directory, push ls -l of it
                    $basedir = substr( $basedir, 0, -1 );
                    preg_match( '/\s+FileType:\s(\w+)\s/', $responses, $matches );
                    $fileType = $matches[ 1 ];
                    # we could add data to this entry, e.g. timestamp, size etc
                    preg_match( '/\s+Size:\s(\d+)\s/', $responses, $matches );
                    $size = $matches[ 1 ];
                    preg_match( '/Modify:\s(.{18,19} \d{4})/', $responses, $matches );
                    $modify = $matches[ 1 ];
                    preg_match( '/Create:\s(.{18,19} \d{4})/', $responses, $matches );
                    $create = $matches[ 1 ];
                    $info = [
                        "size" => $size
                        ,"create" => $create
                        ,"modify" => $modify
                        ];
                    
                    debugecho( "for $basedir filetype is $fileType" );
                    switch( $fileType ) {
                        case "Directory" : {
                            $cmds[] = [ "runcmd" => "ls -l '$basedir'" ];
                            $cmds[] = [ "waitfor" => "foldershare> " ];
                            $info[ "depth" ] = count( explode( "/", $basedir ) ) - 2;
                            array_push( $g_info[ "seedmelab" ][ "dirs" ], [ $basedir => $info ] );
                        }
                        break;

                        case "Regular" : {
                            array_push( $g_info[ "seedmelab" ][ "files" ], [ $basedir => $info ] );
                        }
                        break;
                        
                        default : {
                            echo "unsupported fileType $fileType\n";
                            exit( 4 );
                        }
                        break;
                    }
                }
                break;

                case "rm " : # remove file, nothing to respond to (could check errors?)
                    break;

                case "rmd" : # remove directory, nothing to respond to (could check errors?)
                    break;

                case "mkd" : # create directory, nothing to respond to (could check errors?)
                    break;

                case "put" : # upload file, nothing to respond to (could check errors?)
                    break;
                    
                default : {
                    echo "unsupported lastcmd '$lastcmd'\n";
                    exit( 3 );
                }
                break;
            }
            $lastcmd = "";
        }
    }

    debugecho( "next cmd" );
    if ( !count( $cmds ) ) {
        return 0;
    }

    $cmd = array_shift( $cmds );
    foreach ( $cmd as $key => $value ) {
        debugecho( "key $key value $value" );
        switch ( $key ) {
            case "waitfor" : {
                array_push( $waitfor, $value );
            }
            break;
            case "runcmd" : {
                $responses = "";
                array_push( $towrite, $value );
                $lastcmd = $value;
                $waitone++;
            }
            break;
            default : {
                debugecho( "Unsupported command type '$key'" );
                exit( 2 );
            }
            break;
        }
    }
    return 1;
}
    
# command loop

function process_cmds () {
    global $pipes;
    global $waitfor;
    global $towrite;
    global $responses;
    global $process;    

    # are we setup?

    if ( !nextcmd() ) {
        echo "Empty command stack!\n";
        exit;
    }

    # process until cmds exhausted

    do {
        debugecho( "select loop" );
        
        $read = [ 
            $pipes[ 2 ] 
            ];

        if ( count( $waitfor ) ) {
            array_push( $read, $pipes[ 1 ] );
        }

        if ( count( $towrite ) ) {
            $write = [
                $pipes[ 0 ]
                ];
        } else {
            $write = [];
        }
        $except = [];

        $retval = stream_select( $read, $write, $except, 1 );
        debugecho( "select loop return value : $retval" );
        if ( !$retval ) {
            $status = proc_get_status( $process );
            if ( !$status[ 'running' ] ) {
                echo "Error, process died\n";
                exit;
            }
            continue;
        }

        # there are streams to process
        if ( in_array( $pipes[ 1 ], $read ) ) {
            $responses .= stream_get_contents( $pipes[ 1 ] );
            if ( strpos( $responses, $waitfor[ 0 ] ) ) {
                array_shift( $waitfor );
                if ( !nextcmd() ) {
                    return;
                }
            }
        }
        
        if ( in_array( $pipes[ 2 ], $read ) ) {
            echo "Error returned by process:" . stream_get_contents( $pipes[ 2 ] );
            exit( 1 );
        }

        if ( in_array( $pipes[ 0 ], $write ) ) {
            fwrite( $pipes[ 0 ], array_shift( $towrite ) . "\n");
            if ( !nextcmd() ) {
                return;
            }
        }
    } while ( 1 );
}

# get local fs

function scan_fs( $dir = "" ) {
    global $g_info;
    global $fs;

    $use_dir = $fs;
    if ( strlen( $dir ) ) {
        $use_dir = $fs . "/" . $dir;
    }

    $sdir = scandir( $use_dir );
    foreach ( $sdir as $key => $value ) {
        if ( !in_array( $value, [ ".", ".." ] ) ) {
            $name = $dir .  "/" . $value;
            $stat = stat( $use_dir . "/" . $value );
            $info = 
                [ 
                  "size" => $stat[ "size" ]
                  ,"create" => $stat[ "ctime" ]
                  ,"modify" => $stat[ "mtime" ]
                ];

            if ( is_dir( $use_dir . "/" . $value ) ) {
                array_push( $g_info[ "fs" ][ "dirs" ], [ $name => $info ] );
                scan_fs( $name );
            } else {
                array_push( $g_info[ "fs" ][ "files" ], [ $name => $info ] );
            }
        }
    }                
}

function local_fs() {
    debugecho( "local_fs", 0 );
    scan_fs();
}

function info_array( $source, $type ) {
    global $g_info;

    $result = [];

    foreach ( $g_info[ $source ][ $type ] as $key => $value ) {
        $result[] = implode( "", array_keys( $value ) );
    }
    return $result;
}
    
function remove_from_seedmelab_nonexistent() {
    global $cmds;

    debugecho( "remove_from_seedmelab_nonexistent", 0 );

    $seedmelabfiles = info_array( "seedmelab", "files" );
    $seedmelabdirs  = info_array( "seedmelab", "dirs" );
    $fsfiles      = info_array( "fs", "files" );
    $fsdirs       = info_array( "fs", "dirs" );

    $removefiles  = array_diff( $seedmelabfiles, $fsfiles );
    $removedirs   = array_reverse( array_diff( $seedmelabdirs, $fsdirs ) );

    debugecho( "remove files:\n" . implode( "\n", $removefiles ), 0 );
    debugecho( "remove dirs:\n" . implode( "\n", $removedirs ), 0 );

    foreach ( $removefiles as $value ) {
        $cmds[] = [ "runcmd" => "rm '$value'" ];
        $cmds[] = [ "waitfor" => "foldershare> " ];
    }

    foreach ( $removedirs as $value ) {
        $cmds[] = [ "runcmd" => "rmdir '$value'" ];
        $cmds[] = [ "waitfor" => "foldershare> " ];
    }

    debugecho( "remove commands:\n" . json_encode( $cmds, JSON_PRETTY_PRINT ), 0 );
}

function create_directories_on_seedmelab() {
    global $cmds;

    debugecho( "create_directories_on_seedmelab", 0 );

    $seedmelabdirs  = info_array( "seedmelab", "dirs" );
    $fsdirs       = info_array( "fs", "dirs" );

    $createdirs   = array_diff( $fsdirs, $seedmelabdirs );

    debugecho( "createdirs dirs:\n" . implode( "\n", $createdirs ), 0 );

    foreach ( $createdirs as $value ) {
        $cmds[] = [ "runcmd" => "mkdir '$value'" ];
        $cmds[] = [ "waitfor" => "foldershare> " ];
    }

    debugecho( "create directories commands:\n" . json_encode( $cmds, JSON_PRETTY_PRINT ), 0 );
}
    
function upload_files_to_seedmelab() {
    global $cmds;
    global $fs;

    debugecho( "upload_files_to_seedmelab", 0 );

    $seedmelabfiles  = info_array( "seedmelab", "files" );
    $fsfiles       = info_array( "fs", "files" );

    $uploadfiles   = array_diff( $fsfiles, $seedmelabfiles );

    debugecho( "upload files:\n" . implode( "\n", $uploadfiles ), 0 );

    foreach ( $uploadfiles as $value ) {
        $dest = preg_replace( "/\/[^\/]+$/", "", $value );
        $cmds[] = [ "runcmd" => "put '${fs}$value' '$dest'" ];
        $cmds[] = [ "waitfor" => "foldershare> " ];
    }

    debugecho( "upload files commands:\n" . json_encode( $cmds, JSON_PRETTY_PRINT ), 0 );
}
    
# work thru the stages
# stages are
#  1: get seedmelab fs info
#  2: get local fs info
#  3: optionally remove nonexistent on fs from seedmelab
#  4: create directories not present on seedmelab
#  5: upload files not present on seedmelab or differeing in size

$startatstage  = 1;
$finishatstage = 5;

function stage_loop() {
    global $g_info;
    global $finishatstage;
    global $startatstage;
    global $cmds;
    global $options;
    
    for ( $stage = $startatstage; $stage <= $finishatstage; $stage++ ) {
        debugecho( "stage $stage", 0 );

        switch ( $stage ) {
            case 1 : { # getseedmelab fs info
                $cmds = [
                    [ "waitfor" => "foldershare> " ]
                    ,[ "runcmd" => "ls -l" ]
                    ,[ "waitfor" => "foldershare> " ]
                    ];
                process_cmds();
            }
            break;
            
            case 2 : { # get local fs info
                local_fs();
            }
            break;

            case 3 : { # optionally remove nonexistent on fs from seedmelab
                if ( isset( $options[ "r" ] ) ) {
                    remove_from_seedmelab_nonexistent();
                    if ( count( $cmds ) ) {
                        process_cmds();
                    }
                } else {
                    debugecho( "stage skipped", 0 );
                }
            }
            break;

            case 4 : { # create directories on seedmelab
                create_directories_on_seedmelab();
                if ( count( $cmds ) ) {
                    process_cmds();
                }
            }
            break;

            case 5 : { # upload files to seedmelab
                upload_files_to_seedmelab();
                if ( count( $cmds ) ) {
                    process_cmds();
                }
            }
            break;

            default : { # invalid stage
                debugecho( "stage invalid", 0 );
            }
            break;
        }

    }

    debugecho( "stage loop done", 0 );
    debugecho( json_encode( $g_info, JSON_PRETTY_PRINT ), 1 );

    exit;
}

stage_loop();

