<?php
class AdodbLiteDatabaseEngine extends DatabaseEngineBase {
   protected $mDebugMessage; // special property for adodb_lite driver

   function __construct($dbConfig = NULL) {
      parent::__construct($dbConfig);

      SysLog::Instance()->log("creating AdodbLiteDatabaseEngine", "database");

      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/adodb_lite/adodb.inc.php';

      $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC; // this should be in config, but how?
      SysLog::Instance()->log("AdodbLiteDatabaseEngine::Preparing dbtype:".$this->mDbConfig['db_type']);
      $this->mrDbConnection = ADONewConnection($this->mDbConfig['db_type']);

      // set debug mode via configuration
      $this->mrDbConnection->debug = (bool) $this->mDbConfig['db_debug_enabled'];

      SysLog::Instance()->log("AdodbLiteDatabaseEngine::Done preparing dbtype:".$this->mDbConfig['db_type']);
   }

   private function GetParsedSqlHelper($value) {
      if (is_null($value)) {
         return 'NULL';
      } elseif (is_string($value) || is_object($value)) { // note: we have to handle sanitizer instances
         return '\\\'' . addslashes($value) . '\\\'';
      } else {
         return "$value";
      }
   }

   protected function GetParsedSql($sql, $params) {
      // no params? just return the $sql
      if (count($params) == 0)
         return $sql;

      // processing params
      $param_serialized = '';
      foreach ($params as $k => $v) {
         if (is_array($v)) {
            $v_list_string = '';
            foreach ($v as $d) {
               $v_list_string .= $this->GetParsedSqlHelper($d) . ', ';
            }
            $param_serialized .= '\'' . substr($v_list_string, 0, -2) . '\', ';
         } else {
            $param_serialized .= '\'' . $this->GetParsedSqlHelper($params[$k]) . '\', ';
         }
      }

      // normalize the sql, replacing '%...' with %s
      $sql = preg_replace('/%[bcdufoxX]/', '%s', $sql);
      $sql = preg_replace('/\'%s\'/', '%s', $sql);

      eval('$sql_parsed = sprintf("' . $sql . '", ' . substr($param_serialized, 0, -2) . ');');

      return $sql_parsed;
   }

   protected function GetCacheIdentifier($sql, $params) {
      return $this->GetParsedSql($sql, $params);
   }

   public function Connect() {
      $port = ($this->mDbConfig['db_port'] != '') ? ':' . $this->mDbConfig['db_port'] : '';
      return $this->mrDbConnection->Connect($this->mDbConfig['db_host'] . $port,
         $this->mDbConfig['db_user'], $this->mDbConfig['db_pass'], $this->mDbConfig['db_name'], true);
   }

   public function Disconnect() {
      return $this->mrDbConnection->Close();
   }

   public function StartTrans() {
      return $this->mrDbConnection->StartTrans();
   }

   public function EndTrans($condition) {
      return $this->mrDbConnection->CompleteTrans($condition);
   }

   public function Open($sql, $params) {
      $this->mDebugMessage = '';
      if ($this->mrDbConnection->debug)
         ob_start();
      $sql_parsed = $this->GetParsedSql($sql, $params);
      $rs = $this->mrDbConnection->Execute($sql_parsed);
      if ($this->mrDbConnection->debug) {
         $this->mDebugMessage = strip_tags(ob_get_contents());
         ob_end_clean();
      }
      if ($rs) {
         return $rs->GetArray();
      } else {
         return FALSE;
      }
   }

   public function Execute($sql, $params) {
      $this->mDebugMessage = '';
      if ($this->mrDbConnection->debug)
         ob_start();
      $sql_parsed = $this->GetParsedSql($sql, $params);
      $rs = $this->mrDbConnection->Execute($sql_parsed);
      if ($this->mrDbConnection->debug) {
         $this->mDebugMessage = strip_tags(ob_get_contents());
         ob_end_clean();
      }
      if ($rs) {
         return TRUE;
      } else {
         return FALSE;
      }
   }

   public function AffectedRows() {
      return $this->mrDbConnection->Affected_Rows();
   }

   public function LastInsertId() {
      return $this->mrDbConnection->Insert_ID();
   }

   public function SetDebugOn() {
      $this->mrDbConnection->debug = TRUE;
   }

   public function SetDebugOff() {
      $this->mrDbConnection->debug = FALSE;
   }

   public function GetLastError() {
      if ($this->mDebugMessage != '') // debug message is always superior than error message
         return $this->mDebugMessage;
      if ($this->mrDbConnection)
         return $this->mrDbConnection->ErrorNo() . ': ' . $this->mrDbConnection->ErrorMsg();
      return 'An error occured when instantiating ' . $this->mDbConfig['db_driv'] . ' driver.';
   }
}
?>
