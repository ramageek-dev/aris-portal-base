<?php
class SessionSso {
   private static $mrInstance;

   private $mSessionSsoSavePath;
   private $mId;
   private $mSsoSequence;
   private $mName;

   private $mLocalCacheEnabled = FALSE; // might be implemented in the future

   private function __construct() {
      $this->mSessionSsoSavePath = Configuration::Instance()->GetValue('application',
         'session_sso_session_save_path');
      if (!$this->mSessionSsoSavePath ||
          !file_exists(Configuration::Instance()->GetValue('application',
            'session_sso_session_save_path'))) {
         $this->mSessionSsoSavePath = Configuration::Instance()->GetTempDir();
      }
      $this->mName = Configuration::Instance()->GetValue('application', 'session_sso_name');
      Configuration::Instance()->Load('sso_app_db.conf.php');
   }

   function FileName() {
      list($app_id, $sso_id, $seq) = explode('-', $this->mId);
      return $this->mSessionSsoSavePath . '/sso_' . $sso_id;
   }

   function UpdateLocalSsoInfo() {
      $data = serialize(array('username' => $_SESSION['username'],
         'is_logged_in' => $_SESSION['is_logged_in']));
      $f = fopen($this->FileName(), 'w');
      if ($f) {
         fwrite($f, $data);
         fclose($f);
      }
   }

   /*function UpdateMasterSsoInfo() {
      $data = serialize(array('username' => $_SESSION['username'],
         'is_logged_in' => $_SESSION['is_logged_in']));

   }*/

   function RetrieveSsoInfoFromMaster() {
      list($app_id, $sso_id, $seq) = explode('-', $this->mId);
      $found = FALSE;
      foreach (Configuration::Instance()->GetValue('sso_app_db') as $key => $value) {
         if ($key == $app_id) {
            $found = TRUE;
            break;
         }
      }
      if (!$found) {
         return FALSE;
      }

      $post_data = http_build_query(array('retr' => $this->mId));
      $context_opt = array('http' => array('method' => 'POST',
         'header' => 'Content-type: application/x-www-form-urlencoded',
         'content' => $post_data));
      $context = stream_context_create($context_opt);
      $data = file_get_contents(Configuration::Instance()->GetValue('sso_app_db', $app_id, 'script_uri') .
         'sso.php', FALSE, $context);
      if ($data) {
         return unserialize($data);
      } else {
         return FALSE;
      }
   }

   function DeleteSsoInfoOnMaster() {
      list($app_id, $sso_id, $seq) = explode('-', $this->mId);
      $found = FALSE;
      foreach (Configuration::Instance()->GetValue('sso_app_db') as $key => $value) {
         if ($key == $app_id) {
            $found = TRUE;
            break;
         }
      }
      if (!$found) {
         return FALSE;
      }

      $post_data = http_build_query(array('dele' => $this->mId));
      $context_opt = array('http' => array('method' => 'POST',
         'header' => 'Content-type: application/x-www-form-urlencoded',
         'content' => $post_data));
      $context = stream_context_create($context_opt);
      $data = file_get_contents(Configuration::Instance()->GetValue('sso_app_db', $app_id, 'script_uri') .
         'sso.php', FALSE, $context);
      if ($data == 'success') {
         return TRUE;
      } else {
         return FALSE;
      }
   }

   function RetrieveSsoInfoFromLocal() {
      $data = file_get_contents($this->FileName());
      if ($data) {
         return unserialize($data);
      } else {
         return FALSE;
      }
   }

   function CreateSsoId() {
      $this->mId = Configuration::Instance()->GetValue('application', 'application_id') .
         '-' . md5(uniqid(rand(), TRUE));
      if ($this->mLocalCacheEnabled) {
         $this->mId .= '-' . $this->GetNextSsoSequence();
      }
      setcookie($this->mName, $this->mId, 0, '/',
         Configuration::Instance()->GetValue('application', 'session_sso_domain_base'),
         Configuration::Instance()->GetValue('application', 'session_cookie_params',
            'secure'));
   }

   /*function UpdateSsoId() {
      list($app_id, $sso_id, $seq) = explode('-', $this->mId);
      $this->mId = $app_id . '-' . $sso_id . '-' . $this->GetNextSsoSequence();
      setcookie($this->mName, $this->mId, 0, '/',
         Configuration::Instance()->GetValue('application', 'session_sso_domain_base'),
         Configuration::Instance()->GetValue('application', 'session_cookie_params',
            'secure'));
   }*/

   function GetNextSsoIdSequence() {
      return time();
   }

   function Start() {
      if (!Configuration::Instance()->GetValue('application', 'session_sso_enabled')) {
         return FALSE;
      }

      if (!isset($_COOKIE[$this->mName])) {
         $this->CreateSsoId();
      } else {
         $this->mId = $_COOKIE[$this->mName];
         if ($this->mLocalCacheEnabled) {
            if (!file_exists($this->FileName())) {
               if ($sso_data = $this->RetrieveSsoInfoFromMaster()) {
                  // apply user info here
                  $_SESSION['username'] = $sso_data['username'];
                  $_SESSION['is_logged_in'] = $sso_data['is_logged_in'];
               } else {
                  $this->CreateSsoId();
               }
            } else {
               $last_modified = filemtime($this->FileName());
               if (time() - $last_modified > Session::Instance()->Expire() * 60) { // expired?
                  // try to update from master first
                  if ($sso_data = $this->RetrieveSsoInfoFromMaster()) {
                     // apply user info here
                     $_SESSION['username'] = $sso_data['username'];
                     $_SESSION['is_logged_in'] = $sso_data['is_logged_in'];
                  } else {
                     $this->CreateSsoId();
                  }
               } else {
                  if ($sso_data = $this->RetrieveSsoInfoFromLocal()) {
                     // apply user info here
                     $_SESSION['username'] = $sso_data['username'];
                     $_SESSION['is_logged_in'] = $sso_data['is_logged_in'];
                  } else {
                     $this->CreateSsoId();
                  }
               }
            }
         } else {
            if ($sso_data = $this->RetrieveSsoInfoFromMaster()) {
               // apply user info here
               $_SESSION['username'] = $sso_data['username'];
               $_SESSION['is_logged_in'] = $sso_data['is_logged_in'];
            } else {
               $this->CreateSsoId();
            }
         }
      }

      list($app_id, $sso_id, $seq) = explode('-', $this->mId);
      if ($this->mLocalCacheEnabled ||
          Configuration::Instance()->GetValue('application', 'application_id') == $app_id) {
         register_shutdown_function(array($this, 'UpdateLocalSsoInfo'));
      }
   }

   function DispatchRequest() {
      if (!Configuration::Instance()->GetValue('application', 'session_sso_enabled')) {
         return FALSE;
      }

      if (isset($_POST['retr'])) {
         $this->SendSsoInfo($_POST['retr']);
      } elseif (isset($_POST['dele'])) {
         $this->DeleteSsoFile($_POST['dele']);
      }
   }

   function DeleteSsoFile($id) {
      $this->mId = $id;
      return unlink($this->FileName());
   }

   function SendSsoInfo($id) {
      // for security shake
      // this feature requires that HostnameLookups on Apache configuration must be turned on
      if (Configuration::Instance()->GetValue('application', 'session_sso_do_hostname_lookup')) {
         $legal = FALSE;
         foreach (Configuration::Instance()->GetValue('sso_app_db') as $value) {
            if ($value['hostname'] == $_SERVER['REMOTE_HOST'] &&
                $value['ip_address'] == $_SERVER['REMOTE_ADDR']) {
               $legal = TRUE;
               break;
            }

         }
         if (!$legal) {
            return FALSE;
         }
      }

      $this->mId = $id;

      if (file_exists($this->FileName())) {
         $last_modified = filemtime($this->FileName());
         if (!(time() - $last_modified > Configuration::Instance()->GetValue('application',
             'session_expire') * 60)) { // expired?
            echo serialize($this->RetrieveSsoInfoFromLocal());
            touch($this->FileName());
         }
      }
   }

   function TakeOverSsoMaster() {
      if (!Configuration::Instance()->GetValue('application', 'session_sso_enabled')) {
         return FALSE;
      }

      $this->DeleteSsoInfoOnMaster();
      $this->CreateSsoId();
      register_shutdown_function(array($this, 'UpdateLocalSsoInfo'));
   }

   static function Instance() {
      if (!isset(self::$mrInstance))
         self::$mrInstance = new SessionSso();

      return self::$mrInstance;
   }
}
?>