<?php


class SysLogTcpIo extends SysLogIo {
   function __construct() {
      parent::__construct();

      /* Create a TCP/IP socket. */
      if (Configuration::Instance()->GetValue( 'application','syslog_enabled')) {
         $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
         if ($socket < 0) {
            die("[SysLog] socket_create() failed: reason: " . socket_strerror($this->socket) . "\n");
         }

         //echo "Attempting to connect to '$address' on port '$service_port'...";
         $result = socket_connect($this->socket, Configuration::Instance()->GetValue( 'application', 'syslog_tcp_host'), Configuration::Instance()->GetValue('application', 'syslog_tcp_port'));
         if ($result < 0) {
            die("[SysLog] socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n");
         }
      }
   }

   function Read() {
      return false; // no read method on TcpIo!
   }

   function Write($log, $category) {
      if (in_array($category, Configuration::Instance()->GetValue( 'application','syslog_category')) || (count(Configuration::Instance()->GetValue( 'application','syslog_category')) == 0) ) {
         $app_detail = '[AI_'.Configuration::Instance()->GetValue( 'application','application_id').'_UI_'.Configuration::Instance()->GetValue( 'application','unit_id').']';
         $in = $app_detail."[".$log['timestamp']."][".$category."] ".$log['log'];
         //$in = print_r($log, true);
         $in .= "\n";
         socket_write($this->socket, $in, strlen($in));

         //socket_close($this->socket);
      }
   }
}

?>