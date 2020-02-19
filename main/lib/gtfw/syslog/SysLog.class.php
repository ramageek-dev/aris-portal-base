<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/syslog/SysLogAbstract.class.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/syslog/LogItem.class.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/syslog/SysLogIo.class.php';

class SysLog extends SysLogAbstract {
   static private $mInstance = null;
   private $mQueryLog;

   function __construct() {   
      $this->mQueryLog="";
      parent::__construct();

      // make sure mrIo is initiated so subsquence call to this object doesn't fail
      $this->mrIo = new SysLogIo();

      if (Configuration::Instance()->GetValue('application','syslog_enabled')){
         switch(Configuration::Instance()->GetValue('application','syslog_io_engine')) {
            case 'tcp':
               require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
                  'main/lib/gtfw/syslog/SysLogTcpIo.class.php';
               $this->mrIo = new SysLogTcpIo(); // default IO
               break;
            case 'file':
               require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
                  'main/lib/gtfw/syslog/SysLogFileIo.class.php';
               $this->mrIo = new SysLogFileIo(); // default IO
               break;
            default:
               require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
                  'main/lib/gtfw/syslog/SysLogStdIo.class.php';
               $this->mrIo = new SysLogStdIo(); // default IO
               break;
         }
      }
   }

   function SetIo($sysLogIo) {
      $this->mrIo = $sysLogIo;
   }

   function DoLog($message = "", $category = "public") {
      if (!Configuration::Instance()->GetValue('application','syslog_enabled'))
         return;
      
      if( ! ini_get('date.timezone') )
      {
         date_default_timezone_set('GMT');
      } 
      $timestamp = date('Y/m/d h:i:s');
      $logitem = new LogItem();
      $logitem->newLog();
      $logitem->SetLogTimestamp($timestamp);
      $logitem->SetLogType($category);
      $logitem->SetLogMessage($message);

      $this->_saveLog($category, $logitem->getLogData());
   }

   // private
   private function _saveLog($category, $log) {
      $this->mrIo->Write($log, $category);
   }

   // private
   function _getLogs() {
      return $this->mrIo->read();
   }

   function GetRawLog($category = "") {
      $logs = $this->_getLogs();
      if (!is_array($log))
         return array();
      $res = array();
      if ($category != "") {
         if (is_array($category) && (count($category)>0)) {
            // filter array
            foreach($logs as $key=>$item)
               //if (array_key_exists($key, $logs))
               if (in_array($key, $category))
                  $res = array_merge($res, $item);
         } else {
            // filter single category
            $res = $logs[$category];
         }
      } else {
         // merge all
         if (is_array($logs) && (count($logs)>0)) {
            foreach($logs as $key=>$item)
               $res = array_merge($res, $item);
         }
      }

      // sort logs
      if (is_array($res))
         usort($res, create_function('$a, $b', 'if ($a[\'idx\'] == $b[\'idx\']) return 0; return ($a[\'idx\'] < $b[\'idx\']) ? -1 : 1;'));

      return $res;
   }
   
   public function addQueryLog($message){
   	$this->mQueryLog[]=$message;
   }
   
   public function getAllError(){
   	return $this->mQueryLog;
   }

   static function Instance() {
      if (!isset(self::$mInstance))
         self::$mInstance = new SysLog();

       return self::$mInstance;
   }

   static function Log($message , $category) {
      self::Instance()->DoLog($message , $category);
   }
}

?>
