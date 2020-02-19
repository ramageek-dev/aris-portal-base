<?php
class DatabaseEngineBase implements DatabaseEngineIntf {
   protected $mDbConfig;

   protected $mrDbConnection;

   function __construct($dbConfig = NULL) {
      $this->mDbConfig = $dbConfig;

      // preparing result cache configuration
      $this->mDbConfig['db_result_cache_lifetime'] = $this->mDbConfig['db_result_cache_lifetime'] != '' ? (int) $this->mDbConfig['db_result_cache_lifetime'] : 0;// no recursive dependencies between db and session // Session::Instance()->Expire() * 60; // defaults to session lifetime
      $this->mDbConfig['db_result_cache_path'] = file_exists($this->mDbConfig['db_result_cache_path']) ? $this->mDbConfig['db_result_cache_path'] : Configuration::Instance()->GetTempDir();
      //
   }

   // Warning: do not rely on this magic method
   // Might be deleted in the future
//   private function __call($name, $args) {
   public function __call($name, $args) {	   //by apris => diganti ke public
      if (method_exists($this->mrDbConnection, $name)) {
         return call_user_func_array(array(&$this->mrDbConnection, $name), $args);
      } else {
         return FALSE;
      }
   }

   // override these methods below!

   protected function GetCacheIdentifier($sql, $params) {
   }

   // connect/disconnect
   public function Connect() {
   }

   public function Disconnect() {
   }

   // transaction
   public function StartTrans() {
   }

   public function EndTrans($condition) {
   }

   // database operation
   public function Open($sql, $params, $varMarker = NULL) {
   }

   /**
    * this method offers a default result caching technique,
    * but you can override this method as needed when the default isn't satisfying
    * @param string sql, within the form for sprintf
    * @param array params
    */
   public function OpenCache($sql, $params, $varMarker = NULL) {
      $sql_parsed = $this->GetCacheIdentifier($sql, $params);

      if (($result = $this->GetCache($sql_parsed)) === FALSE) {
         SysLog::Log('cache MISS for '.$sql, 'cache');
         // cache miss
         $result = $this->Open($sql, $params, $varMarker);
         if ($result !== FALSE)
            $this->CacheResult($sql_parsed, $result); // cache valid only result
      } else
         SysLog::Log('cache HIT for '.$sql, 'cache');

      SysLog::Log("OpenCache returns:\n".print_r($result, true), 'cache');

      return $result;
   }

   public function Execute($sql, $params, $varMarker = NULL) {
   }

   public function AffectedRows() {
   }

   public function LastInsertId() {
   }

   // misc.
   public function SetDebugOn() {
   }

   public function SetDebugOff() {
   }

   public function GetLastError() {
   }

   // default result caching technique, can be overrided as needed
   // TODO: enabling file lock mechanism
   protected function CacheResult($sql, $result) {
      // this guarantees that different application (distinguished by application_id)
      // will have different cache for the same query
      $app_id = Configuration::Instance()->GetValue('application', 'application_id');
      $app_id = ($app_id == '') ? 'NULL' : $app_id;

      $cache_id = md5($sql . $app_id);
      $fname = $this->mDbConfig['db_result_cache_path'] . '/resultcache_'. $cache_id;
      $content = serialize($result); // you can do what ever you want here
      $fh = fopen($fname, 'w+');
      fwrite($fh, $content);
      fclose($fh);
   }

   protected function GetCache($sql) {
      // this guarantees that different application (distinguished by application_id)
      // will have different cache for the same query
      $app_id = Configuration::Instance()->GetValue('application', 'application_id');
      $app_id = ($app_id == '') ? 'NULL' : $app_id;

      $cache_id = md5($sql . $app_id);
      $fname = $this->mDbConfig['db_result_cache_path'] . '/resultcache_' . $cache_id;

      if (file_exists($fname)) {
         if (!$this->CacheIsStillValid($fname))
            return false;

         $fh = fopen($fname, 'r');
         while (!feof($fh)) {
            $buf = fread($fh, 1024);
            $content.=$buf;
         }
         fclose($fh);

         return unserialize($content);
      } else {
         return false;
      }
   }

   /**
   * Check wether soapcache is still valid.
   * from what Session has
   * @param string $fname
   * @return boolean false and deltes soapcache when cache no longer valid, return true otherwise
   */
   protected function CacheIsStillValid($fname) {
      $last_modified = filemtime($fname);
      if (time() - $last_modified > $this->mDbConfig['db_result_cache_lifetime']) { // expired?
         unlink($fname);
         return false;
      } else
         return true;
   }
   // end of default result caching technique

   // deprecated but still supported, do not override!
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
}
?>