<?php

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/security/authentication_method/Authenticable.intf.php';

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/security/authentication_method/UserInfoIntf.class.php';

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/security/authentication_method/UserInfo.class.php';

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/security/authentication_method/AuthenticationBase.class.php';

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/security/authorization_method/Authorizable.intf.php';

/**
* Security Class (singleton)
* @package Security
* @author Akhmad Fathonih
* @version 1.0
* @copyright 2006&copy;Gamatechno
*/

class Security {
   public $mSecurityEnabled;

   private $mUserId;
   private $mRealName;
   private $mUserName;
   private $mPassword;
   private $mNoPassword;
   private $mActive;
   private $mForceLogout;

   private $mLoggedIn;
   private $mAuth;

   private $mCurrentRequestId = NULL;
   private $mLastRequestId = NULL;

   private static $mrSecurityInstance;
   private $mrSecurityHelper;

   static function Instance() {
      if (!isset(self::$mrSecurityInstance))
         self::$mrSecurityInstance = new Security();

      return self::$mrSecurityInstance;
   }

   static function Authentication() {
      return self::$mrSecurityInstance->mAuthentication;
   }

   static function Authorization() {
      return self::$mrSecurityInstance->mAuthorization;
   }
   
   function ModuleDenied($module){
   	return self::$mrSecurityInstance->mAuthorization->ModuleDenied($module);
   }

   private function __construct() {

      $this->mSecurityEnabled = (bool) Configuration::Instance()->GetValue('application', 'enable_security');

      if ($this->mSecurityEnabled) { // when we do need security things

         $method = Configuration::Instance()->GetValue('application', 'authentication_method');

         $method = empty($method) ? 'default' : $method; // fallback to default
         SysLog::Instance()->log('Security: authentication_method=' . $method, 'login');
         require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
            'main/lib/gtfw/security/authentication_method/' . $method . '/Authentication.class.php';

         $this->mAuthentication = new Authentication();

         $method = Configuration::Instance()->GetValue('application', 'authorization_method');
         $method = empty($method) ? 'default' : $method; // fallback to default
         SysLog::Instance()->log('Security: authorization_method='.$method, 'authorization');
         require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
            'main/lib/gtfw/security/authorization_method/' . $method . '/Authorization.class.php';

         $this->mAuthorization = new Authorization();

         // ensure these two session vars is set
         $_SESSION['is_logged_in'] = $this->IsLoggedIn();
         $_SESSION['username'] = $this->mAuthentication->GetCurrentUser()->GetUserName();
      } else { // create dummy objects
         require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
            'main/lib/gtfw/security/NullSecurity.class.php';
         SysLog::Instance()->Log('Security: authentication_method=none', 'login');
         $this->mAuthentication = new NullSecurity();
         SysLog::Instance()->Log('Security: authorization_method=none', 'authorization');
         $this->mAuthorization = new NullSecurity();
      }

      if ($this->IsUsingRequestId())
         $this->GenerateRequestId();
   }

   function IsUsingRequestId() {
      return (Configuration::Instance()->GetValue('application', 'enable_request_id') &&
          Session::Instance()->IsStarted());
   }

   private function GenerateRequestId() {
      if ($this->IsUsingRequestId()) {
         if (isset($_SESSION['req_id'])) {
            $this->mLastRequestId = $_SESSION['req_id'];
         }
         $_SESSION['req_id'] = mt_rand();
         $this->mCurrentRequestId = $_SESSION['req_id'];
      }
   }

   function GetCurrentRequestId() {
      return $this->mCurrentRequestId;
   }

   function IsRequestIdValid() {
      $req_id_is_ok = TRUE;
      if ($this->IsUsingRequestId() && $this->mLastRequestId !== NULL) {
         $req_id_is_ok = isset($_REQUEST['_' . $this->mLastRequestId]);
      }

      return $req_id_is_ok;
   }

   function AllowedToAccess($module, $subModule, $action, $type) {
      
      if ($this->mSecurityEnabled == false) {
         $result = TRUE;
      } else {

         $this->mAuthentication->CheckForceLogout();

         $this->mAuthorization->SetUserName($this->mAuthentication->GetCurrentUser()->GetUserName());

         #$this->mAuthorization->SetUserId($this->mAuthentication->GetCurrentUser()->GetUserId());
         $this->mAuthorization->SetApplicationId($this->mAuthentication->GetCurrentUser()->GetApplicationId());
         $result = $this->mAuthorization->IsAllowedToAccess($module, $subModule, $action, $type);
      }

      return $result &&
         ($this->IsRequestIdValid() || Configuration::Instance()->GetValue('application', 'selective_request_id'));
   }

   function IsProtocolCheckPassed($module, $subModule, $action, $type, $connectionNumber = 0) {
      if (!$this->mSecurityEnabled)
         return true;

//       if (!$this->mrSecurityHelper) {
//          require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
//             'main/lib/gtfw/security/SecurityHelper.class.php';
//          $this->mrSecurityHelper = new SecurityHelper($connectionNumber);
//       }
	  return true;
      //return $this->mrSecurityHelper->IsProtocolCheckPassed($module, $subModule, $action, $type);
   }

   function Login($username, $password, $hashed = true) {
      SysLog::Instance()->log('Security: login by Login()', 'login');
      SysLog::Instance()->log('Security: enabled='.$this->mSecurityEnabled, 'login');

      if (!$this->mSecurityEnabled)
         return TRUE;

      $this->mAuthentication->SetUserName($username);
      $this->mAuthentication->SetPassword($password, $hashed);

      if ($this->mAuthentication->DoLogin()) {
         SysLog::Instance()->log('Security: logged in', 'login');
      } else {
         SysLog::Instance()->log('Security: crap, cannot login', 'login');
         ///TODO: set error on why cannot login
      }
      // ensure these two session vars is set
      $_SESSION['is_logged_in'] = $this->IsLoggedIn();
      $_SESSION['username'] = $this->mAuthentication->GetCurrentUser()->GetUserName();
      $_SESSION['aktive_user_group_id'] = $this->mAuthentication->GetCurrentUser()->GetActiveUserGroupId();
      SessionSso::Instance()->TakeOverSsoMaster();

      return $this->mAuthentication->IsLoggedIn();
   }

   // spoiler...
   function LoginEx($authData) {
      SysLog::Instance()->log('Security: login by LoginEx()', 'login');
      SysLog::Instance()->log('Security: enabled='.$this->mSecurityEnabled, 'login');

      if (!$this->mSecurityEnabled)
         return TRUE;

      $this->mAuthentication->SetAuthenticationData($authData);

      if ($this->mAuthentication->DoLogin()) {
         SysLog::Instance()->log('Security: logged in', 'login');
      } else {
         SysLog::Instance()->log('Security: crap, cannot login', 'login');
         ///TODO: set error on why cannot login
      }
      // ensure these two session vars is set
      $_SESSION['is_logged_in'] = $this->IsLoggedIn();
      $_SESSION['username'] = $this->mAuthentication->GetCurrentUser()->GetUserName();
      SessionSso::Instance()->TakeOverSsoMaster();

      return $this->mAuthentication->IsLoggedIn();
   }
   //////

   function Logout($destroySession = FALSE) {
      if (!$this->mSecurityEnabled)
         return TRUE;

      if ($this->mAuthentication->DoLogout($destroySession)) {
         SysLog::Instance()->log('Security: logged out', 'login');
         $result = true;
      } else {
         $result = false;
      }
      // ensure these two session vars is set
      $_SESSION['is_logged_in'] = $this->IsLoggedIn();
      $_SESSION['username'] = $this->mAuthentication->GetCurrentUser()->GetUserName();

      if ((bool) Configuration::Instance()->GetValue('application', 'session_multiuser_enabled')) {
         Session::Instance()->RegenerateSessionDirId();
      }

      SessionSso::Instance()->TakeOverSsoMaster();

      return $result;
   }

   function RequestDenied() {
   	$dbMsg = SysLog::Instance()->getAllError();
   	print_r($dbMsg);
      if ($this->IsLoggedIn()) {
         echo "Request denied, insufficient access permission!";
         SysLog::Instance()->log('Security: Request denied, insufficient access permission!', 'security');
      } else {
         // hasn't logged in yet or session expired, do redirect to login page

         if ($_SERVER['HTTP_X_GTFWXHRREQUESTSIGNATURE'] != '') {
            // yes, it is breaking the http rule as the http 401 error isn't meant
            // to use with redirection, but this http error code comes handy
            // when handling xhr request since the xhr object transparently
            // follow http redirection which isn't desired by this framework
            // so, an http 401 error will include a redirection header
            // at the end, instead of embedding page within page, user will be
            // redirected to login page
            $error_code = 401;
         } else {
            $error_code = NULL;
         }

         Redirector::RedirectToUrl($this->mAuthentication->GetLoginPage(), TRUE, $error_code, FALSE);
      }
   }

   function ModuleAccessDenied() {
      echo 'You are not allowed to access this module';
   }

   function IsLoggedIn() {
      if (!$this->mSecurityEnabled)
         return FALSE;

      SysLog::Instance()->log('Security::IsLoggedIn: '.$this->mAuthentication->IsLoggedIn(), 'login');

      return $this->mAuthentication->IsLoggedIn();
   }

   function RequestSalt() {
      return $this->mAuthentication->GetSalt();
   }
   
   function logedByOpenId($userName){
   	$this->mAuthentication->setLogedByOpenId($userName);
   }
}
?>
