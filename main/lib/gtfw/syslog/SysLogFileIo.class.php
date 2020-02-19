<?php
class SysLogFileIo extends SysLogIo {
   function read() {
      $contents = @file(Configuration::Instance()->GetValue( 'application', 'syslog_log_path')."syslog.log");
      if (!is_array($content))
         return false;
      foreach($contents as $line) {
         $item = unserialize($line);
         $result[$item['category']][] = unserialize($item['content']);
      }

      return $result;
   }

   function write($log, $category) {
      if (Configuration::Instance()->GetValue( 'application','syslog_enabled')) {
         $handle = fopen(Configuration::Instance()->GetValue( 'application', 'syslog_log_path')."syslog.log", "a+");
         $item = array("category" => $category, "content" => serialize($log));
         fwrite($handle, serialize($item)."\n");
      }
   }
}

?>