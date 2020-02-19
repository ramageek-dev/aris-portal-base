<?php
class TestDbDatabaseEngine extends DatabaseEngineBase {
   protected $mErrorMessage; // special property for test_db driver
   protected $mRecordSet; // special property for test_db driver

   function __construct($dbConfig = NULL) {
      parent::__construct($dbConfig);

      SysLog::Instance()->log("Creating TestDbDatabaseEngine", "testdb");
      SysLog::Instance()->log("TestDbDatabaseEngine created", "testdb");
   }

   protected function GetParsedSql($sql, $params) {
      SysLog::Instance()->log("About to parse SQL: $sql, and we have " . count($params) . " param(s)", "testdb");

      // no params? just return the $sql
      if (count($params) == 0)
         return $sql;

      // processing params for IN clause and adding slashes to strings
      foreach ($params as $k => $v) {
         if (is_array($v)) {
            $params[$k] = '~~' . join("~~,~~", $v) . '~~';
            $params[$k] = str_replace('~~', '\'', addslashes($params[$k]));
         } else {
            $params[$k] = addslashes($params[$k]);
         }
      }
      $param_serialized = '~~' . join("~~,~~", $params) . '~~';
      $param_serialized = str_replace('~~', '\'', addslashes($param_serialized));
      eval('$sql_parsed = sprintf("' . $sql . '", ' . $param_serialized . ');');

      SysLog::Instance()->log("SQL parsed: " . $sql_parsed, "testdb");
      return $sql_parsed;
   }

   protected function GetCacheIdentifier($sql, $params) {
      return $this->GetParsedSql($sql, $params);
   }

   public function Connect() {
      SysLog::Instance()->log("Connecting to record set", "testdb");

      $record_set_found = file_exists($this->mDbConfig['db_record_set_file']);
      $this->mErrorMessage = $record_set_found ? '' : 'Not connected to record set. Record set file not found!';

      if ($record_set_found) {
         SysLog::Instance()->log("Retrieving record set", "testdb");

         require_once $this->mDbConfig['db_record_set_file'];
         $this->mRecordSet = $sql;
         unset($sql);

         SysLog::Instance()->log("Record set retrieved", "testdb");
      }

      return $record_set_found;
   }

   public function Disconnect() {
      return TRUE;
   }

   public function StartTrans() {
      return TRUE;
   }

   public function EndTrans($condition) {
      return TRUE;
   }

   public function Open($sql, $params, $prefer_cache = false) {
      SysLog::Instance()->log("About to open SQL", "testdb");
      $sql_parsed = $this->GetParsedSql($sql, $params);
      SysLog::Instance()->log("Opening SQL: $sql_parsed", "testdb");
      $rs = $this->mRecordSet[$sql_parsed];
      SysLog::Instance()->log("Record set returned: " . print_r($rs, TRUE), "testdb");
      if ($rs) {
         return $rs;
      } else {
         $this->mErrorMessage = 'An error occured when opening SQL';
         return FALSE;
      }
   }

   public function Execute($sql, $params) {
      SysLog::Instance()->log("About to execute SQL", "testdb");
      $sql_parsed = $this->GetParsedSql($sql, $params);
      SysLog::Instance()->log("Executing SQL: $sql_parsed", "testdb");
      $rs = $this->mRecordSet[$sql_parsed];
      SysLog::Instance()->log("SQL executed", "testdb");

      if ($rs) {
         return TRUE;
      } else {
         $this->mErrorMessage = 'An error occured when executing SQL';
         return FALSE;
      }
   }

   public function AffectedRows() {
      return NULL;
   }

   public function LastInsertId() {
      return NULL;
   }

   public function SetDebugOn() {
      SysLog::Instance()->log("Debug is not available", "testdb");
   }

   public function SetDebugOff() {
      SysLog::Instance()->log("Debug is not available", "testdb");
   }

   public function GetLastError() {
      if ($this->mErrorMessage != '')
         return $this->mErrorMessage;
      return 'An error occured when instantiating ' . $this->mDbConfig['db_driv'] . ' driver.';
   }
}
?>