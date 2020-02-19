#!/usr/bin/php -q
<?php
error_reporting(E_ERROR);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

if ($argc > 1) {
   $address = $argv[1];
   $port = $argv[2];
} else {
   $address = 'localhost';
   $port = 9777;
}

$monitoring_port = $port + 1;

$server_msg=<<<_MSG_

SysLog Log Server 0.1
Akhmad Fathonih (toni@gamatechno.com)
SysLog is a part of GTFW Framework
Gamatechno (c) 2006

_MSG_;

$gtfw_message = <<<_GTFW_
+OK Running on $address using port $port
+OK Waiting for connection from GTFW and slave client...

_GTFW_;

$monitoring_message = <<<_GTFW_
+OK Waiting for debug log from GTFW ...

_GTFW_;

echo "$server_msg\n$gtfw_message";

// gtfw server log
if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
   echo "-ERR socket_create() failed: reason: " . socket_strerror($sock) . "\n";
}

if (($ret = socket_bind($sock, $address, $port)) < 0) {
   echo "-ERR socket_bind() failed: reason: " . socket_strerror($ret) . "\n";
}

if (($ret = socket_listen($sock, 5)) < 0) {
   echo "-ERR socket_listen() failed: reason: " . socket_strerror($ret) . "\n";
}

// // monitoring server log
// if (($mon_sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
//    echo "-ERR socket_create() failed: reason: " . socket_strerror($mon_sock) . "\n";
// }
//
// if (($ret = socket_bind($mon_sock, $address, $monitoring_port)) < 0) {
//    echo "-ERR socket_bind() failed: reason: " . socket_strerror($ret) . "\n";
// }
//
// if (($ret = socket_listen($mon_sock, 5)) < 0) {
//    echo "-ERR socket_listen() failed: reason: " . socket_strerror($ret) . "\n";
// }

do {
//    if (($mon_client_sock = socket_accept($mon_sock)) < 0) {
//        echo "socket_accept() failed: reason: " . socket_strerror($mon_client_sock) . "\n";
//        break;
//    } else {
//       $mon_clients[] = $mon_client_sock;
//       $msg = "$server_msg\n$monitoring_message";
//       socket_write($mon_client_sock, $msg, strlen($msg));
//       echo "+OK New monitoring client connected\n";
//    }

   if (($msgsock = socket_accept($sock)) < 0) {
       echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
       break;
   } else {
      /* Send instructions. */
      $msg = "\nSysLog TCP/IP Server\n" .
         "To quit, type 'quit'. To shut down the server type 'shutdown'.\n";
      socket_write($msgsock, $msg, strlen($msg));

      echo "+OK Client connected\n";

      do {
         if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
            //echo "-ERR socket_read() failed: reason: " . socket_strerror($msgsock) . "\n";
            break 1;
         }
         if (!$buf = trim($buf)) {
            continue;
         }
         if ($buf == 'quit') {
            break;
         }
         if ($buf == 'shutdown') {
            socket_close($msgsock);
            break 2;
         }
   //        $talkback = "+OK\n";//"PHP: You said '$buf'.\n";
   //        socket_write($msgsock, $talkback, strlen($talkback));
         // echo to server console
         echo "$buf\n";

         // propagate to clients
         $buf.="\n";
//          if (is_array($mon_clients))
//             foreach($mon_clients as $client_sock)
//                socket_write($client_sock, $buf, strlen($buf));
      } while (true);
      // print +OK
      $buf.="\n+OK\n";
//       if (is_array($mon_clients))
//          foreach($mon_clients as $client_sock)
//             socket_write($client_sock, $buf, strlen($buf));
      echo "$buf";
   }
} while (true);

// close monitoring clietns
// if (is_array($mon_clients))
//    foreach($mon_clients as $client_sock)
//       socket_close($client_sock);
//
// // close slave server
// socket_close($mon_sock);

// close gtfw client
socket_close($msgsock);

// close log server
socket_close($sock);
?>
