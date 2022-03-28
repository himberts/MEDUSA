#!/usr/local/bin/php
<?php

// todo: monitor connections and on close, remove any associated topic keys
$GLOBALS[ "MAXTEXTAREANOTICE" ] = "The messages are truncated at the top due to large size\n";
$GLOBALS[ "MAXTEXTAREALEN" ] = 512000;
$GLOBALS[ "MAXTEXTAREALEN" ] = 0 ? intval( "__textarea:maxlen__" ) : $GLOBALS[ "MAXTEXTAREALEN" ];
if ( $GLOBALS[ "MAXTEXTAREALEN" ] > 10000000 ) {
   $GLOBALS[ "MAXTEXTAREALEN" ] = 10000000;
}
$GLOBALS[ "MAXTEXTAREATRUNC" ] = -$GLOBALS[ "MAXTEXTAREALEN" ];
$GLOBALS[ "MAXTEXTAREALEN" ] += strlen( $GLOBALS[ "MAXTEXTAREANOTICE" ] );

$json = json_decode( file_get_contents( "/opt/genapp/mxray/appconfig.json" ) );

$lockdir = "/var/run/genapp";
if ( isset( $json->lockdir ) ) {
    $lockdir = $json->lockdir;
}

// check if already running and register pid
define('LOCK_FILE', "$lockdir/msg-ws-" . $json->messaging->zmqport . ".lock");
define('EXPECTED_CMDLINE', "phpmsg-wsserver.php" );

function tryLock() {
    # If lock file exists, check if stale.  If exists and is not stale, return TRUE
    # Else, create lock file and return FALSE.

    if (@symlink("/proc/" . getmypid(), LOCK_FILE) !== FALSE) # the @ in front of 'symlink' is to suppress the NOTICE you get if the LOCK_FILE exists
    {   
        return true;
    }

    # link already exists
    # check if it's stale
    $isstale = false;

    if ( is_link(LOCK_FILE) ) {
        echo "is_link(" . LOCK_FILE . ") true\n";
        if ( ( $link = readlink( LOCK_FILE ) ) === FALSE ) {
            $isstale = true;
            echo "is stale 1\n";
        }
    } else {
        $isstale = true;
        echo "is stale 2\n";
    }

    if ( !$isstale && is_dir( $link ) ) {
        # make sure the cmdline exists & matches expected
        $cmdline_file = $link . "/cmdline";
        echo "cmdline_file = $cmdline_file\n";
        if ( ($cmdline = file_get_contents( $cmdline_file )) === FALSE ) {
            echo "could not get contents of $cmdline_file\n";
            $isstale = true;
            echo "is stale 3\n";
        } else {
            # remove nulls
            $cmdline = str_replace("\0", "", $cmdline);
            if ( $cmdline != EXPECTED_CMDLINE ) {
                echo "unexpected contents of $cmdline_file\n";
                $isstale = true;
                echo "is stale 4 \n";
            }
        }
    }            
        
    if (is_link(LOCK_FILE) && !is_dir(LOCK_FILE)) {
        $isstale = true;
    }

    if ( $isstale ) {
        unlink(LOCK_FILE);
        # try to lock again
        return tryLock();
    }
    return false;
}

if ( !tryLock() ) {
   die( "Already running.\n" );
}

# remove the lock on exit (Control+C doesn't count as 'exit'?)
register_shutdown_function( 'unlink', LOCK_FILE );

require '../vendor/autoload.php';

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

// connect
# connect
require_once "/var/www/html/mxray/ajax/ga_db_lib.php";

$keeptrying = 0;
do {
    if ( !ga_db_status( ga_db_open() ) ) {
        echo "msg_wsserver: could not connect to mongodb, sleeping 15s\n";
        sleep( 15 );
        $keeptrying = 1;
    }
} while ( $keeptrying );



class Pusher implements WampServerInterface {
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();

    public function onOpen(ConnectionInterface $conn) {

    }
    public function onClose(ConnectionInterface $conn) {

    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console

        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console

        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {

    }

# // here's where we start

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        # // When a visitor subscribes to a topic link the Topic object in a  lookup array


        if ( substr( $topic->getId(), 0, 6 ) == 'unsub:' )
        { 
           $tmp = substr( $topic->getId(), 6 );

           unset( $this->subscribedTopics[ $tmp ] );
        } else {
#//           if ( $doc = ga_db_output( ga_db_findOne( 'cache', 'msgs', [ "_id" => $topic->getID() ] ) ) )
#//           {
#//
#//               $conn->send( $doc[ 'data' ] );
#//
#//           } else {
#//
#//           }

           if (!array_key_exists($topic->getId(), $this->subscribedTopics)) {
              $this->subscribedTopics[$topic->getId()] = $topic;
           }
        }
    }

    public function onUnSubscribe(ConnectionInterface $conn, $submsg) {

      if (array_key_exists((string) $submsg, $this->subscribedTopics)) {

         unset( $this->subscribedTopics[ (string) $submsg ] );
      } else {

      }


    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onMsgPost($postmsg) {
        $postData = json_decode($postmsg, true);

        if ( !isset( $postData[ '_uuid'  ] ) ) {
            echo "Error: no _uuid received : $postmsg\n";
            return;
        }







        

        if ( isset( $postData[ '_pid'   ] ) &&
             isset( $postData[ '_app'   ] ) &&
             isset( $postData[ '_where' ] ) &&
             isset( $postData[ '_what'  ] ) ) {
            
           if ( !ga_db_status(
                     ga_db_update(
                         'running',
                         $postData[ '_app' ],
                         [ "_id" => $postData[ '_uuid' ] ],
                         [ 
                           '$push' => [
                               "pid" => 
                               [ 
                                 "where" => $postData[ '_where' ],
                                 "pid"   => $postData[ '_pid'   ],
                                 "what"  => $postData[ '_what'  ]
                               ]
                           ]
                         ],
                         [ "upsert" => true ]
                     )
                )
               ) {
                
            }
            return;
        }

        # // ignore if cancelled

        if ( ga_db_output( 
                 ga_db_findOne( 
                     'cancel',
                     'msgs',
                     [ "_id" => $postData[ '_uuid' ] ]
                 )
             )
            ) {
            
            return;
        }

        if ( isset( $postData[ "_cancel" ] ) ) {
            
            if ( !ga_db_status(
                      ga_db_insert(
                          'cancel',
                          'msgs',
                          [ "_id" => $postData[ '_uuid' ] ]
                      )
                 )
                ) {
                echo "Error: Could not insert to msgs->cancel for " . $postData[ '_uuid' ] . " " . $ga_db_errors;
            }
        }            

        // re-send the data to all the clients subscribed to that category



        if ( $doc =
             ga_db_output(
                 ga_db_findOne(
                     'cache',
                     'msgs',
                     [ "_id" => $postData[ '_uuid' ] ]
                     )
             )
            ) {
            $textprepend = "";
            $textcurrent = isset( $postData[ '_textarea' ] ) ? $postData[ '_textarea' ] : "";
            if ( isset( $doc[ 'data' ] ) ) {
                $docjson = json_decode( $doc[ 'data' ] );
                if ( isset( $docjson->_textarea ) ) {
                    $textprepend = $docjson->_textarea;
                }
            }
            $texttot = $textprepend . $textcurrent;
            $textlen = strlen( $texttot );
            if ( $textlen ) {
                if ( $textlen > $GLOBALS[ "MAXTEXTAREALEN" ] ) {
                    $texttot = $GLOBALS[ "MAXTEXTAREANOTICE" ] . substr( $texttot, $GLOBALS[ "MAXTEXTAREATRUNC" ] );
                }
                $toPostData = $postData;
                $toPostData[ '_textarea' ] = $texttot;
                $postmsg = json_encode( $toPostData );

            }
        }

        if ( !ga_db_status(
                  ga_db_update(
                      'cache',
                      'msgs',
                      [ '_id' => $postData['_uuid'] ],
                      [ '$set' => [ 'data' => $postmsg ] ],
                      [ 'upsert' => true ]
                  )
             )
            ) {
            echo "mongo save exception $e\n";
        }
        
        if (!array_key_exists( $postData[ '_uuid' ], $this->subscribedTopics ) ) {

            return;
        }


        $topic = $this->subscribedTopics[$postData['_uuid']];

        $topic->broadcast($postData);
    }
}


$loop   = React\EventLoop\Factory::create();
$pusher = new Pusher;

// Listen for the web server to make a ZeroMQ push after an ajax request
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://' . $json->messaging->zmqhostip . ':' . $json->messaging->zmqport ); // Binding to 127.0.0.1 means the only client that can connect is itself
$pull->on('message', array($pusher, 'onMsgPost'));

// Set up our WebSocket server for clients wanting real-time updates
$webSock = new React\Socket\Server($loop);
$webSock->listen( $json->messaging->wsport, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Ratchet\Wamp\WampServer(
                $pusher
            )
        )
    ),
    $webSock
);

echo "msg_wsserver: listening WS port:" . $json->messaging->wsport . " receiving ZMQ port: " . $json->messaging->zmqport . PHP_EOL;

$loop->run();
