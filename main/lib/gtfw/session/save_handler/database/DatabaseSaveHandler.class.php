<?php

class DatabaseSaveHandler extends Database implements SessionSaveHandlerIntf {
   private $mStorage;

   function __construct($connectionNumber = 0) {
      $connection_id = Configuration::Instance()->GetValue('application', 'session_db_connection');
      $this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/session/save_handler/database/session.sql.php';
      parent::__construct($connectionNumber);

      SysLog::Log('DbSaveHandler::__construct', 'Session');
   }

   function SessionOpen($savePath, $sessionName) {
      // not necessary as it's already done in constructor
      return $this->Connect();
   }

   function SessionClose() {
      return $this->Disconnect();
   }

   function SessionRead($sessionId) {
      SysLog::Log('Do I have any Db instance here ('.$sessionId.'): ' . get_class(), 'Session');

      $result = $this->Open($this->mSqlQueries['read_data'], array($sessionId));

      SysLog::Log('Reading session data('.$sess_id.') using query: '.$this->mSqlQueries['read_data'].'=> ' . print_r($result, true), 'Session');

      if (!isset($result[0]))
         return ''; // obey the rule, return '' on error
      else {
         SysLog::Log('Returning session data('.$sessionId.'): ' . print_r($result[0], true), 'Session');
         return base64_decode($result[0]['SessionData']);
      }
   }

   function SessionWrite($sessionId, $sessionData) {

      $sessionData = base64_encode($sessionData);
      $result = $this->Open($this->mSqlQueries['read_data'], array($sessionId));

      if (!isset($result[0])) {
         SysLog::Log('Insert session data('.$sessionId.'): ' . $sessionData, 'Session');
         $result = $this->Execute($this->mSqlQueries['insert_data'], array($sessionId, $sessionData, date('Y:m:d H:i:s')));
      } else {
         SysLog::Log('Updating session data('.$sessionId.'): ' . $sessionData, 'Session');
         $result = $this->Execute($this->mSqlQueries['update_data'], array($sessionData, date('Y:m:d H:i:s'), $sessionId));
      }

      if (!$result)
         return FALSE;
      else
         return TRUE;
   }

   function SessionDestroy($sessionId) {
      SysLog::Log('Destroy session('.$sessionId.')', 'Session');
      $result = $this->Execute($this->mSqlQueries['delete_data'], array($sessionId));
      if (!$result)
         return TRUE;
      else
         return FALSE;
   }

   function SessionGc($maxExpireTime) {
      SysLog::Log('Garbage collecting...', 'Session');
      $result = $this->Execute($this->mSqlQueries['gc_data'], array(time(), $maxExpireTime));
      if (!$result)
         return TRUE;
      else
         return FALSE;
   }

   function IsSessionExpired($sessionId) {
      $result = $this->Open($this->mSqlQueries['read_data'], array($sessionId));

      if (!$result) {
         return TRUE;
      } else {
         $last_modified = strtotime($result[0]['SessionCTime']);
         if (time() - $last_modified > Session::Instance()->Expire() * 60) { // expired?
            return TRUE;
         } else {
            return FALSE;
         }
      }
   }
}
?>
