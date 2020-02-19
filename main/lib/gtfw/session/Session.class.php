<?php

// helper function
function GtfwSess() {
   return Session::Instance();
}
//

class Session {
   // these variables are considered as private
   var $mrConfig;
   var $mSessionName;
   var $mSessionSavePath;
   var $mSessionExpire;
   var $mSessionIsStarted;
   var $mUseSession;

   private static $mrInstance;

   // NOTE: dirty hack to temporarily stop configuration hook
   private $mBaseDirHookStop = FALSE;
   ////

   private $mMultiUserEnabled;
   private $mOriginalBaseDir;
   private $mSessionDirId;
   private $mSaveHandler;
   public $mSaveHandlerObj;
   public $mBasedirConfigHook;
   public $mSessionSaveHandlerConfigHook;


   private function __construct() {
      $this->mUseSession = Configuration::Instance()->GetValue('application', 'use_session');
      $this->mSessionIsStarted = FALSE;
   }

   function FileName() {
      $filename = ($this->Id() != '') ? $this->Id() : $_COOKIE[$this->Name()];
      return $this->SavePath() . '/sess_' . $filename;
   }

   function Name($name = NULL) {
      // set session name
      if (isset($name)) {
         session_name($name);
         $this->mSessionName = $name;
         return TRUE;
      }
      // get session name
      $session_name = session_name();
      if (!$session_name) {
         $session_name = Configuration::Instance()->GetValue('application', 'session_name');
      }

      return $session_name;
   }

   function SavePath($savePath = NULL) {
      // set session save path
      if (!empty($savePath)) {
         session_save_path($savePath);
         $this->mSessionSavePath = $savePath;
         return TRUE;
      }
      // get session save path
      $save_path = session_save_path();
      $save_path = $save_path ? $save_path : ini_get('session.save_path');

      if (!$save_path)
         $save_path = Configuration::Instance()->GetTempDir();

      return $save_path;
   }

   function Expire($expire = NULL) {
      // set session expire
      if (isset($expire)) {
         session_cache_expire($expire);
         $this->mSessionExpire = $expire;
         return TRUE;
      }
      // get session save path
      return session_cache_expire();
   }

   function CookieParams($lifeTime = NULL, $path = NULL, $domain = NULL, $secure = NULL) {
      // get cookie params
      if ($lifeTime == NULL && $path == NULL && $domain == NULL && $secure == NULL) {
         return session_get_cookie_params();
      } else { // set cookie params
         session_set_cookie_params($lifeTime, $path, $domain, $secure);
         return TRUE;
      }
   }

   function Start() {
      if (!$this->mUseSession)
         return TRUE;
      
      $this->Name(Configuration::Instance()->GetValue('application', 'session_name'));
      $this->SavePath(Configuration::Instance()->GetValue('application', 'session_save_path'));
      $this->Expire(Configuration::Instance()->GetValue('application', 'session_expire'));
      $session_cookie_param = Configuration::Instance()->GetValue('application', 'session_cookie_params');

      // prevents data from being destryoed by garbage collector. session.gc_maxlifetime issue: http://id.php.net/manual/en/function.session-cache-expire.php#57098
      ini_set('session.gc_maxlifetime', $session_cookie_param['lifetime']);

      // session expiration check
      // now based on last modify time
      // note: this should not be bound to internal session, but not in this near future ;)
      $session_expired = FALSE;
      if (isset($_COOKIE[$this->Name()])) {
         if (Configuration::Instance()->GetValue('application', 'session_save_handler') == 'default') {
            $session_file = $this->FileName();
            if (file_exists($session_file)) {
               $last_modified = filemtime($session_file);
               if (time() - $last_modified > $this->Expire() * 60) { // expired?
                  $session_expired = TRUE;
               }
            } else {
               $session_expired = TRUE;
            }
         } else {
            if($this->mSaveHandlerObj!='')
            $session_expired = $this->mSaveHandlerObj->IsSessionExpired($_COOKIE[$this->Name()]);
         }

         if ($session_expired) { // regenerate session id
            unset($_COOKIE[$this->Name()]);
         }
      }

      if ((bool) Configuration::Instance()->GetValue('application', 'session_multiuser_enabled')) {
         $this->mMultiUserEnabled = (bool) Configuration::Instance()->GetValue('application', 'session_multiuser_enabled');
         // preserve original base directory
         $this->mOriginalBaseDir = Configuration::Instance()->GetValue('application', 'basedir');
         // register configuration hook for $application['basedir']
         require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
            'main/lib/gtfw/session/configuration_hook/BasedirConfigHook.class.php';
         $this->mBasedirConfigHook = new BasedirConfigHook();
         Configuration::Instance()->RegisterHook($this->mBasedirConfigHook);

         if (!isset($_COOKIE[$this->Name()]) ||
            $this->mOriginalBaseDir == dirname($_SERVER['REQUEST_URI']) . '/' ||
            $this->mOriginalBaseDir == $_SERVER['REQUEST_URI'] ||
            $this->mOriginalBaseDir == $_SERVER['REQUEST_URI'] . '/') {
            // assume new session
            // destroy old session (paranoia) & create session directory id
            setcookie($this->Name(), '',
               time() - ($this->Expire() * 60) - 42000,
               $session_cookie_param['path'],
               $session_cookie_param['domain'],
               $session_cookie_param['secure']);
            unset($_COOKIE[$this->Name()]);
            $this->mSessionDirId = $this->CreateSessionDirId();
         } else {
            // assume already created session
            // guess the session directory id
            // normally, every request will be like this: '/session/id/index.php'
            $session_dir = $this->mOriginalBaseDir . Configuration::Instance()->GetValue('application', 'sessiondir');
            if (dirname($_SERVER['REQUEST_URI']) . '/' == $session_dir) {
               $this->mSessionDirId = basename($_SERVER['REQUEST_URI']);
            } else {
               $this->mSessionDirId = basename(dirname($_SERVER['REQUEST_URI']));
            }
         }
      }

      // force session path set to basedir
      $session_cookie_param['path'] = Configuration::Instance()->GetValue('application', 'basedir');
      $this->CookieParams(0, // always zero, to prevent cookie timing difference
         $session_cookie_param['path'],
         $session_cookie_param['domain'],
         $session_cookie_param['secure']);

      if (!isset($this->mSessionName))
         return FALSE;
      if (session_start()) {
         $this->mSessionIsStarted = TRUE;
         
         if ($this->mMultiUserEnabled) {
            
            if (!isset($_SESSION['session_dir_id'])) {
               $_SESSION['session_dir_id'] = $this->mSessionDirId;
            } else {
               if ($this->mSessionDirId != $_SESSION['session_dir_id']) {
                  $this->End();
               }
            }
         }
      }else{
         die("session can't start");
      }
      return $this->mSessionIsStarted;
   }

   function Restart() {
      if (!$this->mUseSession)
         return TRUE;

      return session_regenerate_id(TRUE);
   }

   function Close() {
      if (!$this->mUseSession)
         return TRUE;

      session_write_close();
      $this->mSessionIsStarted = FALSE;
   }

   function Id() {
      return session_id();
   }

   function IsStarted() {
      return $this->mSessionIsStarted;
   }

   function End($destroySession = TRUE) {
      $session_cookie_param = Configuration::Instance()->GetValue('application', 'session_cookie_params');
      // force session path set to basedir
      $session_cookie_param['path'] = Configuration::Instance()->GetValue('application', 'basedir');
      if ($this->mSessionIsStarted) {
         if ($destroySession) {
            if (isset($_COOKIE[$this->mSessionName])) {
               setcookie($this->mSessionName, '',
                  time() - ($this->Expire() * 60) - 42000,
                  $session_cookie_param['path'],
                  $session_cookie_param['domain'],
                  $session_cookie_param['secure']);
            }
         }
         session_unset();
         session_destroy();
         $this->Close();
      }
      return $this->mSessionIsStarted;
   }

   function GetSaveHandler() {
      return $this->mSaveHandler;
   }

   function GetSessionBaseDir() {
      $session_dir_id = $this->mSessionDirId;
      if (!$session_dir_id) {
         // guessing session dir
         $session_dir_id = dirname($_SERVER['REQUEST_URI']);
      }

      $session_base_dir = $this->mOriginalBaseDir;
      if (!$this->mBaseDirHookStop) {
         $session_base_dir .= Configuration::Instance()->GetValue('application', 'sessiondir') .
            $session_dir_id . '/';
      }

      return $session_base_dir;
   }

   function CreateSessionDirId() {
      $session_dir_id = md5(uniqid(rand(), TRUE));

      if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') { // windows family, preferably 2000 & XP
         $command = 'start /b "title" ' .
            escapeshellarg(Configuration::Instance()->GetValue('application', 'session_path_to_linkd') .
            'linkd.exe') . ' ' .
            escapeshellarg(Configuration::Instance()->GetValue('application', 'docroot') .
            Configuration::Instance()->GetValue('application', 'sessiondir') .
            $session_dir_id) . ' ' . escapeshellarg(Configuration::Instance()->GetValue('application', 'docroot')) . ' 2>&1';
         exec($command);
      } else { // assumes *nix family
         symlink(Configuration::Instance()->GetValue('application', 'docroot'),
            Configuration::Instance()->GetValue('application', 'docroot') .
            Configuration::Instance()->GetValue('application', 'sessiondir') .
            $session_dir_id);
      }

      return $session_dir_id;
   }

   function RegenerateSessionDirId() {
      $session_dir_id = $this->mSessionDirId;
      if (!$session_dir_id) {
         // guessing session dir
         $session_dir_id = dirname($_SERVER['REQUEST_URI']);
      }

      if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') { // windows family, preferably 2000 & XP
         $command = 'start /b "title" ' .
            escapeshellarg(Configuration::Instance()->GetValue('application', 'session_path_to_linkd') .
            'linkd.exe') . ' ' .
            escapeshellarg(Configuration::Instance()->GetValue('application', 'docroot') .
            Configuration::Instance()->GetValue('application', 'sessiondir') .
            $session_dir_id) . ' /D 2>&1';
         exec($command);
      } else { // assumes *nix family
         unlink(Configuration::Instance()->GetValue('application', 'docroot') .
            Configuration::Instance()->GetValue('application', 'sessiondir') .
            $session_dir_id);
      }

      $this->mBaseDirHookStop = TRUE;
   }

   function PrepareSaveHandler() {
      $save_handler = Configuration::Instance()->GetValue('application', 'session_save_handler');

      if(empty($save_handler))
         $save_handler = 'default';
         
      $save_handler_class = str_replace(' ', '', ucwords(str_replace('_', ' ' , $save_handler))) .
         'SaveHandler';
      if ($save_handler != 'default') {
         if (file_exists(Configuration::Instance()->GetValue('application', 'gtfw_base') .
               'main/lib/gtfw/session/save_handler/' . $save_handler . '/' .
               $save_handler_class . '.class.php')) {

            require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
               'main/lib/gtfw/session/save_handler/' . $save_handler . '/' .
               $save_handler_class . '.class.php';

            if (class_exists($save_handler_class)) {
               $this->mSaveHandler = $save_handler;

               eval('$this->mSaveHandlerObj = new $save_handler_class();');
               session_set_save_handler(
                  array($this->mSaveHandlerObj, 'SessionOpen'),
                  array($this->mSaveHandlerObj, 'SessionClose'),
                  array($this->mSaveHandlerObj, 'SessionRead'),
                  array($this->mSaveHandlerObj, 'SessionWrite'),
                  array($this->mSaveHandlerObj, 'SessionDestroy'),
                  array($this->mSaveHandlerObj, 'SessionGc')
               );
            } else {
               $this->mSaveHandler = 'default';
            }
         } else {
            $this->mSaveHandler = 'default';
         }
      } else { // default save handler need no initialization
         $this->mSaveHandler = 'default';
      }

      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/session/configuration_hook/SessionSaveHandlerConfigHook.class.php';
      $this->mSessionSaveHandlerConfigHook = new SessionSaveHandlerConfigHook();
      Configuration::Instance()->RegisterHook($this->mSessionSaveHandlerConfigHook);

      return TRUE;
   }

   static function Instance() {
      if (!isset(self::$mrInstance))
         self::$mrInstance = new Session();

      return self::$mrInstance;
   }
}
?>
