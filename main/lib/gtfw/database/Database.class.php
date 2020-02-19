<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/syslog/SysLog.class.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/database/DatabaseEngineIntf.intf.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/database/database_engine/DatabaseEngineBase.class.php';

/**
 * Database
 *
 * @package
 * @author Andronicus Riyono
 * @modified by Yogatama
 * @modified by Akhmad Fathonih
 * @copyright Copyright (c) 2004-2006 Gamatechno
 * @version 3.0
 * @access public
 */
class Database implements DatabaseEngineIntf {
   /**
   * Database Object
   *
   * @var object
   */
   protected $mrDbEngine;

   /**
   * SQL Queries used in this class
   *
   * @var array
   */
   protected $mSqlQueries = array();

   // sql file to use, relative to root directory
   // ex. 'main/lib/gtfw/security/security.sql.php'
   protected $mSqlFile = NULL;

   // is sql file loaded?
   protected $mSqlIsLoaded = FALSE;

   protected $mDbConfig = NULL;

   public $addConn;

   /**
   * DatabaseConnected::DatabaseConnected()
   *
   * Konstruktor DatabaseConnected
   *
   * @param $sqlFile
   * @return
   **/
   public function __construct($connectionNumber = 0) {
      $this->Database($connectionNumber);
   }

   public function Database($connectionNumber = 0) {
      SysLog::Instance()->log("creating Database using connection: #$connectionNumber", "database");

      $db_conn = Configuration::Instance()->GetValue('application', 'db_conn');
      $db_conn = $db_conn[$connectionNumber];
      // remind me
      if (!$db_conn)
         die('Can\'t find database configuration of index ' . $connectionNumber . '. Please, configure it properly in application.conf.php.');
      if ($this->mDbConfig != NULL) {
         foreach ($this->mDbConfig as $key => $value) {
            $db_conn[$key] = $value;
         }
      }
      $this->mDbConfig = $db_conn;
      // makes connection id as part of db config
      $this->mDbConfig['connection_id'] = md5(serialize($this->mDbConfig));
      //
      $db_driv_class = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->mDbConfig['db_driv'])));

      if (!isset($GLOBALS['db'][$this->mDbConfig['connection_id']])) { // not connected yet
         require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
            'main/lib/gtfw/database/database_engine/' . $this->mDbConfig['db_driv'] . '/' . $db_driv_class . 'DatabaseEngine.class.php';
            if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
                  SysLog::Instance()->log('$this->mrDbEngine' . " = new {$db_driv_class}DatabaseEngine(\$this->mDbConfig);", 'database');
                SysLog::Instance()->log('$this->mDbConfig):' . print_r($this->mDbConfig, true), 'database');
                eval('$this->mrDbEngine' . " = new {$db_driv_class}DatabaseEngine(\$this->mDbConfig);");
            } else {
                SysLog::Instance()->log('$this->mrDbEngine' . " = new {$db_driv_class}DatabaseEngine(\$this->mDbConfig);", 'database');
                SysLog::Instance()->log('$this->mDbConfig):' . print_r($this->mDbConfig, true), 'database');
                eval('$this->mrDbEngine' . " = new {$db_driv_class}DatabaseEngine(\$this->mDbConfig);");
            }
         if (!$this->mrDbEngine) {
            SysLog::Log('mrDbEngine is not available, how come?!', 'database');
            die('mrDbEngine is not available!!!');
         }

         if ($this->Connect()) {
            SysLog::Log('Connected using dbid #'.$this->mDbConfig['connection_id'], 'database');
            $GLOBALS['db'][$this->mDbConfig['connection_id']] = $this->mrDbEngine;

            if($connectionNumber!=0)
            	$this->addConn = true;

         } else {
            //die('Can\'t connect to database!');
            SysLog::Instance()->log('Can\'t connect to database!', 'database');
            //die($this->GetLastError());

            if($connectionNumber==0){
            	die('Can\'t connect to database number '.$connectionNumber);
            }else
            	$this->addConn = false;
         }
      } else { // connected already
         SysLog::Log('Already connected using dbid #'.$this->mDbConfig['connection_id'], 'database');
         $this->mrDbEngine = $GLOBALS['db'][$this->mDbConfig['connection_id']];
      }
      // new and old instance have to load sql file!
      $this->LoadSql();
   }

   // warning: this soon to be protected
   public function LoadSql($sqlFile = '') {
      // for awkward compatibility, oops i mean backward compatibility
      if ($this->mSqlFile == '' && $sqlFile == '')
         return FALSE;
      //
      if (!$this->mSqlIsLoaded) {
         $this->mSqlFile = ($sqlFile != '') ? $sqlFile : $this->mSqlFile;
         $default_sql_file = str_replace('{dbe}', '', $this->mSqlFile);
         $default_sql_file = str_replace('//', '/', $default_sql_file);
         $default_sql_file = str_replace('\\', '\\', $default_sql_file);
         if (strpos($this->mSqlFile, '{dbe}') !== FALSE) {
            if (isset($this->mDbConfig['db_type'])) {
               $this->mSqlFile = str_replace('{dbe}', strtolower($this->mDbConfig['db_driv'] . '/' .
                  $this->mDbConfig['db_type']), $this->mSqlFile);
            } else {
               $this->mSqlFile = str_replace('{dbe}', strtolower($this->mDbConfig['db_driv']),
                  $this->mSqlFile);
            }
         }
         // load sql file
         if (file_exists($this->mSqlFile)) {
            require $this->mSqlFile;
         } elseif (file_exists($default_sql_file)) {
            require $default_sql_file;
         } else {
            // this should use gtfw error handler in the future
            die("Required file, either '{$this->mSqlFile}' or '{$default_sql_file}', is not found or is not a file!");
         }

         $this->mSqlQueries = $sql;
         $this->mSqlIsLoaded = TRUE;
         unset($sql);
      }
   }

   // connect/disconnect
   public function Connect() {
      return $this->mrDbEngine->Connect();
   }

   public function Disconnect() {
      return $this->mrDbEngine->Disconnect();
   }

   //check connection
   public function IsConnected(){
	   return $this->mrDbEngine->IsConnected();
	}

   // transaction
   public function StartTrans() {
      return $this->mrDbEngine->StartTrans();
   }

   public function EndTrans($condition) {
      return $this->mrDbEngine->EndTrans($condition);
   }

   // database operation
   public function Open($sql, $params, $varMarker = NULL) {
      return $this->mrDbEngine->Open($sql, $params, $varMarker);
   }

   public function OpenCache($sql, $params, $varMarker = NULL) {
      return $this->mrDbEngine->OpenCache($sql, $params, $varMarker);
   }

   public function Execute($sql, $params, $varMarker = NULL) {
      return $this->mrDbEngine->Execute($sql, $params, $varMarker);
   }

   public function AffectedRows() {
      return $this->mrDbEngine->AffectedRows();
   }

   public function LastInsertId() {
      return $this->mrDbEngine->LastInsertId();
   }

   // deprecated but still supported
   public function ExecuteDeleteQuery($sql, $params) {
      return $this->Execute($sql, $params);
   }

   public function ExecuteUpdateQuery($sql, $params) {
      return $this->Execute($sql, $params);
   }

   public function ExecuteInsertQuery($sql, $params) {
      return $this->Execute($sql, $params);
   }

   public function GetAllDataAsArray($sql, $params) {
      return $this->Open($sql, $params);
   }

   // misc.
   public function SetDebugOn() {
      $this->mrDbEngine->SetDebugOn();
   }

   public function SetDebugOff() {
      $this->mrDbEngine->SetDebugOff();
   }

   public function GetLastError() {
      return $this->mrDbEngine->GetLastError();
   }

   // Warning: do not rely on this magic method
   // Might be deleted in the future
   public function __call($name, $args) {
      SysLog::Instance()->log('Warning: about to call driver\'s specific method (' . $name . ')!', ucwords($this->mDbConfig['db_driv']) . 'DatabaseEngine');
      return call_user_func_array(array(&$this->mrDbEngine, $name), $args);
   }
}
?>
