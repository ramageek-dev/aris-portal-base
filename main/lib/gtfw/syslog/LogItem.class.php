<?php
class LogItem {
   var $mLogTimestamp;
   var $mLogType;
   var $mLogMessage;
   var $mLogIndex;

   function __construct() {
   }

   function newLog() {
   	  $_SESSION['syslog_idx'] = isset($_SESSION['syslog_idx'])?$_SESSION['syslog_idx']:0;
      $_SESSION['syslog_idx'] += 1; // increase log index
      $this->SetLogIndex($_SESSION['syslog_idx']);
   }

   function wrapLogData($logdata) {
      //$data = unserialize($logdata);
      $data = $logdata;
      $this->SetLogTimestamp($data['timestamp']);
      $this->SetLogMessage($data['log']);
      $this->SetLogType($data['type']);
      $this->SetLogIndex($data['idx']);
   }

   function SetLogTimestamp($timestamp) {
      $this->mLogTimestamp = $timestamp;
   }

   function SetLogMessage($message) {
      $this->mLogMessage = $message;
   }

   function SetLogType($type) {
      $this->mLogType = $type;
   }

   function SetLogIndex($idx) {
      $this->mLogIndex = $idx;
   }

   function GetLogTimestamp() {
      return $this->mLogTimestamp;
   }

   function GetLogType() {
      return $this->mLogType;
   }

   function GetLogMessage() {
      return $this->mLogMessage;
   }

   function GetLogIndex() {
      return $this->mLogIndex;
   }

   function getLogData() {
      return array (
                     "timestamp" => $this->GetLogTimestamp(),
                     "log" => $this->GetLogMessage(),
                     "type" => $this->GetLogType(),
                     "idx" => $this->GetLogIndex()
                  );
   }

   static function Instance() {
      if (!isset(self::$mInstance))
         self::$mInstance = new SysLogObj();

       return self::$mInstance;
   }

   static function Log($message , $category) {
      self::Instance()->log($message , $category);
   }
}
?>