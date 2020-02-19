<?php
class SysLogStdIo {
   function Read() {
      return $_SESSION['syslog'];
   }

   function Write($log, $category) {
      $_SESSION["syslog"][$category][] = $log;
   }
}

?>